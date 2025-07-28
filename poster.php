<?php
include 'header.php';

function extractImdbId($input) {
  if (preg_match('/tt\d{7,8}/', $input, $matches)) return $matches[0];
  return '';
}
function extractLocalId($input) {
  if (preg_match('/poster\.php\?id=(\d+)/', $input, $matches)) return (int)$matches[1];
  return 0;
}
function extractYoutubeId($url) {
  if (preg_match('/(?:v=|\/embed\/|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) return $matches[1];
  return '';
}

function pretty_runtime($minutes) {
  $minutes = intval($minutes);
  if ($minutes <= 0) return '';
  $h = floor($minutes / 60);
  $m = $minutes % 60;
  $out = [];
  if ($h > 0) $out[] = "{$h}h";
  if ($m > 0) $out[] = "{$m}mn";
  return implode(' ', $out);
}
function safe($str) {
  return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
function format_imdb_rating($v) {
    return ($v !== null && $v !== '') ? number_format((float)$v, 1) : '—';
}

require_once 'server.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$result = $conn->query("SELECT p.*, pt.label_he AS type_label, pt.icon AS type_icon
  FROM posters p
  LEFT JOIN poster_types pt ON p.type_id = pt.id
  WHERE p.id = $id");
if ($result->num_rows == 0) { echo "<p style='text-align:center;'>❌ פוסטר לא נמצא</p>"; exit; }
$row = $result->fetch_assoc();
$video_id = extractYoutubeId($row['youtube_trailer'] ?? '');
$message = '';

session_start();
$visitor_token = session_id();

$vote_row = $conn->query("
  SELECT vote_type FROM poster_votes 
  WHERE poster_id = $id AND visitor_token = '$visitor_token'
");
$user_vote = $vote_row->num_rows ? $vote_row->fetch_assoc()['vote_type'] : '';

if (isset($_POST['vote'])) {
  $vote = $_POST['vote'];
  if ($vote === 'remove') {
    $conn->query("DELETE FROM poster_votes WHERE poster_id=$id AND visitor_token='$visitor_token'");
    $user_vote = '';
  } elseif (in_array($vote, ['like','dislike'])) {
    if ($user_vote === '') {
      $stmt = $conn->prepare("INSERT INTO poster_votes (poster_id, visitor_token, vote_type) VALUES (?, ?, ?)");
      $stmt->bind_param("iss", $id, $visitor_token, $vote); $stmt->execute(); $stmt->close();
    } else {
      $stmt = $conn->prepare("UPDATE poster_votes SET vote_type=? WHERE poster_id=? AND visitor_token=?");
      $stmt->bind_param("sis", $vote, $id, $visitor_token); $stmt->execute(); $stmt->close();
    }
    $user_vote = $vote;
  }
}

$likes = $conn->query("
  SELECT COUNT(*) as c FROM poster_votes 
  WHERE poster_id=$id AND vote_type='like'
")->fetch_assoc()['c'];

$dislikes = $conn->query("
  SELECT COUNT(*) as c FROM poster_votes 
  WHERE poster_id=$id AND vote_type='dislike'
")->fetch_assoc()['c'];

// פעולות: תגיות, סרטים דומים
if ($_SERVER["REQUEST_METHOD"] === "POST") {
 if (isset($_POST['add_user_tags'])) {
  $g = trim($_POST['user_tags'] ?? '');
  if ($g !== '') {
    $existing_genres = array_map('trim', explode(',', $row['genre']));
    if (in_array($g, $existing_genres)) {
      $message = "⚠️ תגית כבר קיימת בז׳אנרים";
    } else {
      $stmt = $conn->prepare("SELECT 1 FROM user_tags WHERE poster_id = ? AND genre = ?");
      $stmt->bind_param("is", $id, $g); $stmt->execute(); $stmt->store_result();
      if ($stmt->num_rows > 0) {
        $message = "⚠️ תגית כבר קיימת";
      } else {
        $stmt = $conn->prepare("INSERT INTO user_tags (poster_id, genre) VALUES (?, ?)");
        $stmt->bind_param("is", $id, $g); $stmt->execute();
        $message = "✅ תגית נוספה";
      }
      $stmt->close();
    }
  }
}

  if (isset($_POST['remove_user_tags'])) {
    $gid = (int)$_POST['remove_user_tags'];
    $conn->query("DELETE FROM user_tags WHERE id=$gid AND poster_id=$id");
    $message = "🗑️ תגית נמחקה";
  }

  if (isset($_POST['add_similar'])) {
    $input = trim($_POST['similar_input'] ?? '');
    $target_id = 0;
    if (is_numeric($input)) $target_id = (int)$input;
    elseif ($local = extractLocalId($input)) $target_id = $local;
    elseif ($imdb = extractImdbId($input)) {
      $stmt = $conn->prepare("SELECT id FROM posters WHERE imdb_id = ?");
      $stmt->bind_param("s", $imdb); $stmt->execute();
      $res = $stmt->get_result();
      if ($r = $res->fetch_assoc()) $target_id = $r['id'];
      $stmt->close();
    }
if ($target_id > 0 && $target_id != $id) {
  $exists = $conn->prepare("SELECT 1 FROM posters WHERE id = ?");
  $exists->bind_param("i", $target_id);
  $exists->execute();
  $exists->store_result();

  if ($exists->num_rows === 0) {
    $message = "❌ הסרט לא קיים במסד הנתונים";
  } else {
    $check = $conn->prepare("SELECT 1 FROM poster_similar WHERE poster_id=? AND similar_id=?");
    $check->bind_param("ii", $id, $target_id); $check->execute(); $check->store_result();
    if ($check->num_rows == 0) {
      $conn->query("INSERT INTO poster_similar (poster_id, similar_id) VALUES ($id, $target_id)");
      $conn->query("INSERT INTO poster_similar (poster_id, similar_id) VALUES ($target_id, $id)");
      $message = "✅ סרט דומה נוסף";
    } else {
      $message = "⚠️ הקשר כבר קיים";
    }
    $check->close();
  }
  $exists->close();
} else {
  $message = "❌ הסרט לא נמצא";
}
  }

  if (isset($_POST['remove_similar'])) {
    $sid = (int)$_POST['remove_similar'];
    $conn->query("DELETE FROM poster_similar WHERE poster_id=$id AND similar_id=$sid");
    $conn->query("DELETE FROM poster_similar WHERE poster_id=$sid AND similar_id=$id");
    $message = "🗑️ הקשר נמחק";
  }
}

// דומה ואוספים
$similar = [];
$res = $conn->query("SELECT p.* FROM poster_similar ps JOIN posters p ON p.id=ps.similar_id WHERE ps.poster_id=$id");
while ($r = $res->fetch_assoc()) $similar[] = $r;

$collections = [];
$res = $conn->query("
  SELECT c.* FROM poster_collections pc
  JOIN collections c ON c.id = pc.collection_id
  WHERE pc.poster_id = $id
");
while ($r = $res->fetch_assoc()) $collections[] = $r;

// AKA
$display_title_en = $row['title_en'];
if (preg_match('/^(.*?)\s+AKA\s+(.*)$/', $row['title_en'], $m)) {
  $display_title_en = trim($m[1]) . ' AKA ' . trim($m[2]);
}

// --- סדרת TMDb (אם קיימת) ---
$tmdb_collection = null;
$tmdb_collection_movies = [];
if (!empty($row['tmdb_collection_id'])) {
    $tmdb_key = '931b94936ba364daf0fd91fb38ecd91e';
    $col_id = intval($row['tmdb_collection_id']);
    $tmdb_api_url = "https://api.themoviedb.org/3/collection/$col_id?api_key=$tmdb_key&language=he";
    $json = @file_get_contents($tmdb_api_url);
    if ($json) {
        $tmdb_collection = json_decode($json, true);
        if (!empty($tmdb_collection['parts'])) {
            $tmdb_collection_movies = $tmdb_collection['parts'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($display_title_en) ?></title>
  <link rel="stylesheet" href="style.css">
  <style>
    body.rtl { direction: rtl; font-family: Arial; background:#f1f1f1; padding:40px; }
    .poster-page { max-width:800px; margin:auto; background:#fff; padding:20px; border-radius:6px; box-shadow:0 0 6px rgba(0,0,0,0.1);}
    .poster-image { width:200px; float:right; margin-left:20px; border-radius:1px; box-shadow:0 0 4px rgba(0,0,0,0.08);}
    .poster-details { overflow:hidden; }
    .tag { background:#eee; padding:6px 12px; margin:4px; display:inline-block; border-radius:12px; font-size:13px; text-decoration:none; color:#333;}
    button.like-button { cursor: pointer; }
  </style>
</head>
<body class="rtl">
<br>
<div class="poster-page">

  <div style="text-align:left; margin-bottom:10px;">
    <a href="report.php?poster_id=<?= $row['id'] ?>" style="background:#ffdddd; color:#a00; padding:6px 12px; border-radius:6px; font-weight:bold; text-decoration:none;">🚨 דווח על תקלה בפוסטר</a>
  </div>

  <form method="post" style="margin-top:30px;">
  <button type="submit" name="vote" class="like-button" value="like"
    style="background:<?= $user_vote === 'like' ? '#28a745' : '#ccc' ?>; color:white; padding:10px 16px; border:none; border-radius:6px;">
    ❤️ אהבתי (<?= $likes ?>)
  </button>
  <button type="submit" name="vote" class="like-button" value="dislike"
    style="background:<?= $user_vote === 'dislike' ? '#dc3545' : '#ccc' ?>; color:white; padding:10px 16px; border:none; border-radius:6px; margin-right:10px;">
    💔 לא אהבתי (<?= $dislikes ?>)
  </button>
  <?php if ($user_vote): ?>
    <button type="submit" name="vote" class="like-button" value="remove"
      style="background:#666; color:white; padding:10px 16px; border:none; border-radius:6px; margin-right:10px;">
      ❌ בטל הצבעה
    </button>
  <?php endif; ?>
</form><br>

  <?php if ($message): ?>
    <p style="background:#ffe; border:1px solid #cc9; padding:10px; border-radius:6px; color:#444; font-weight:bold;">
      <?= $message ?>
    </p>
  <?php endif; ?>

  <div style="float: right; width: 220px; margin-left: 20px; text-align: right;">
    <?php
    $img = (!empty($row['image_url'])) ? $row['image_url'] : 'images/no-poster.png';
    ?>
    <img src="<?= htmlspecialchars($img) ?>" alt="Poster" style="width: 100%; border-radius: 2px;">
    <div style="margin-top: 10px;">
      <!-- שנה לחיצה -->
      <p><strong>🗓️ שנה:</strong> <a href="home.php?year=<?=urlencode($row['year'])?>"><?= htmlspecialchars($row['year']) ?></a></p>
      <?php if (!empty($row['type_label']) || !empty($row['type_icon'])): ?>
      <p><strong>🎞️ סוג:</strong> <?= htmlspecialchars($row['type_icon'] . ' ' . $row['type_label']) ?></p>
      <?php endif; ?>
      
<p><strong></strong>
  <?php
  include 'languages.php';
  $lang_result = $conn->query("SELECT lang_code FROM poster_languages WHERE poster_id = $id");
  if ($lang_result->num_rows > 0):
    while ($l = $lang_result->fetch_assoc()):
      $code = $l['lang_code'];
      foreach ($languages as $lang) {
        if ($lang['code'] === $code) {
          echo "<a href='language.php?lang_code=" . urlencode($code) . "' style='display:inline-flex; align-items:center; gap:6px; text-decoration:none; margin:4px 0;'>";
          echo "<img src='" . $lang['flag'] . "' alt='" . $lang['label'] . "' style='height:16px;'> ";
          echo "<span>" . $lang['label'] . "</span>";
          echo "</a><br>";
          break;
        }
      }
    endwhile;
  else:
    echo "<span style='color:#999;'>אין סיווגים</span>";
  endif;
  ?>
</p>

      <!-- שפות לחיצות -->
      <p><strong>🔤 שפות:</strong>
      <?php
      $langs = array_filter(array_map('trim', explode(',', $row['languages'] ?? '')));
      foreach ($langs as $lang) {
        echo '<a class="tag" href="language_imdb.php?lang_code='.urlencode($lang).'">'.htmlspecialchars($lang).'</a> ';
      }
      ?>
      </p>
      <!-- מדינות לחיצות -->
      <p><strong>🌎 מדינה:</strong>
      <?php
      $countries = array_filter(array_map('trim', explode(',', $row['countries'] ?? '')));
      foreach ($countries as $country) {
        $disp = str_replace(['United States','United Kingdom'],['USA','UK'],$country);
        echo '<a class="tag" href="country.php?country='.urlencode($country).'">'.htmlspecialchars($disp).'</a> ';
      }
      ?>
      </p>
      
      <?php if (!empty($row['genre'])):
        $genres = explode(',', $row['genre']);
        echo "<p><strong>🎭 ז׳אנר:</strong><br>";
        foreach ($genres as $g):
          $g_clean = trim($g); ?>
          <a href="genre.php?name=<?= urlencode($g_clean) ?>" class="tag"><?= htmlspecialchars($g_clean) ?></a><br>
        <?php endforeach;
        echo "</p>";
      endif; ?>
      
      <!-- 📝 תגיות קהילתיות -->
      <?php
      $res_user = $conn->query("SELECT id, genre FROM user_tags WHERE poster_id = $id");
      if ($res_user->num_rows > 0): ?>
        <p><strong>📝 תגיות משתמשים:</strong><br>
          <?php while ($g = $res_user->fetch_assoc()):
            $g_clean = trim($g['genre']); ?><br>
            <form method="post" style="display:inline;">
              <a href="user_tags.php?name=<?= urlencode($g_clean) ?>" class="tag"><?= htmlspecialchars($g_clean) ?></a>
              <button type="submit" name="remove_user_tags" value="<?= $g['id'] ?>"
                style="border:none; background:none; color:#900; cursor:pointer;">🗑️</button>
            </form>
          <?php endwhile; ?>
        </p>
      <?php endif; ?>

      <!-- ➕ טופס להוספת תגית -->
      <form method="post" style="margin-bottom:20px;">
        <input type="text" name="user_tags" placeholder="הוסף תגית" required>
        <button type="submit" name="add_user_tags">➕ הוסף</button>
      </form>
    </div>
  </div>

  <div class="poster-details">
    <h2>
      <?= htmlspecialchars($display_title_en) ?>
      <?php if (!empty($row['title_he'])): ?><br><?= htmlspecialchars($row['title_he']) ?><?php endif; ?>
    </h2>

    <!-- 🎞️ טריילר מוטמע -->
    <?php if ($video_id): ?>
      <div style="margin-top:30px; text-align:center;">
        <h3>🎞️ טריילר</h3>
        <iframe width="100%" height="315"
          src="https://www.youtube.com/embed/<?= htmlspecialchars($video_id) ?>"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen loading="lazy"></iframe>
      </div>
    <?php else: ?>
      <div style="margin-top:30px; text-align:center;">
        <h3>🎞️ טריילר</h3>
        <img src="images/no-trailer.png" alt="אין טריילר" style="width:300px; border-radius:6px;">
        <p style="color:#888;">אין טריילר זמין כרגע</p>
      </div>
    <?php endif; ?>
    <!-- 🕓 משך זמן -->
    <?php if ($row['runtime']): ?>
      <p><strong>⏱️ משך זמן:</strong> <?= pretty_runtime($row['runtime']) ?></p>
    <?php endif; ?>

    <?php if ($row['is_dubbed'] || $row['has_subtitles']): ?>
      <p>
        <?php if ($row['is_dubbed']): ?>🎙️ מדובב<br><?php endif; ?>
        <?php if ($row['has_subtitles']): ?>📝 כולל כתוביות<?php endif; ?>
      </p>
    <?php endif; ?>

    <p>
      <strong>⭐ IMDb:</strong> <?= format_imdb_rating($row['imdb_rating']) ?><?= $row['imdb_rating'] ? ' / 10' : '' ?>
      | <strong>🔤 IMDb ID:</strong> <?= htmlspecialchars($row['imdb_id']) ?>
    </p>

    <?php if (!empty($row['plot_he'])): ?>
      <p><strong>📝 תקציר (עברית):</strong><br><?= nl2br(htmlspecialchars($row['plot_he'])) ?></p>
    <?php endif; ?>
    <?php if (!empty($row['plot'])): ?>
      <p><strong>📝 תקציר (English):</strong><br><?= nl2br(htmlspecialchars($row['plot'])) ?></p>
    <?php endif; ?>

    <?php
    $roles = [
      'actors'          => '👥 שחקנים',
      'directors'        => '🎬 במאים',
      'writers'          => '✍️ תסריטאים',
      'producers'        => '🎥 מפיקים',
      'cinematographers' => '📷 צלמים',
      'composers'        => '🎼 מלחינים'
    ];
    foreach ($roles as $field => $label) {
      if (!empty($row[$field])) {
        echo "<p><strong>{$label}:</strong> ";
        $items = array_filter(array_map('trim', explode(',', $row[$field])));
        foreach ($items as $i) {
          echo '<a href="actor.php?name=' . urlencode($i) . '" class="tag">' . htmlspecialchars($i) . '</a> ';
        }
        echo "</p>";
      }
    }
    ?>

    <?php if (!empty($row['imdb_link'])): ?>
      <p><strong>🔗 IMDb:</strong>
        <a href="<?= htmlspecialchars($row['imdb_link']) ?>" target="_blank" class="tag">מעבר לקישור</a>
      </p>
    <?php endif; ?>
    <?php if (!empty($row['rt_score'])): ?>
      <p><strong>🍅 Rotten Tomatoes:</strong> <?= htmlspecialchars($row['rt_score']) ?></p>
    <?php endif; ?>
    <?php if (!empty($row['rt_link'])): ?>
      <p><strong>🔗 RT:</strong>
        <a href="<?= htmlspecialchars($row['rt_link']) ?>" target="_blank" class="tag">צפייה באתר</a>
      </p>
    <?php endif; ?>
    <?php if (!empty($row['metacritic_score'])): ?>
      <p><strong>📊 Metacritic:</strong> <?= htmlspecialchars($row['metacritic_score']) ?></p>
    <?php endif; ?>
    <?php if (!empty($row['metacritic_link'])): ?>
      <p><strong>🔗 Metacritic:</strong>
        <a href="<?= htmlspecialchars($row['metacritic_link']) ?>" target="_blank" class="tag">צפייה באתר</a>
      </p>
    <?php endif; ?>


    <!-- אוסף/סדרת סרטים (מקומי) -->
<?php if (count($collections) > 0): ?>
  <h3>🎞️אוספים:</h3>
  <?php foreach ($collections as $c): ?>
    <div>
      <div style="margin-bottom:6px;">
        <a href="collection.php?id=<?= $c['id'] ?>" class="tag"><?= safe($c['name']) ?></a>
      </div>
      
      </div>
    </div>
    <br>
  <?php endforeach; ?>
<?php endif; ?>

<!-- TMDb סדרות -->
<?php
// --- הצגת אוסף TMDB ---
if ($tmdb_collection && count($tmdb_collection_movies) > 1) {
    echo '<h3>🎞️ סרטים בסדרת הסרטים: ' . htmlspecialchars($tmdb_collection['name']) . '</h3>';
    echo '<div style="display: flex; flex-wrap: wrap; gap: 12px;">';
    foreach ($tmdb_collection_movies as $movie) {
        // Link to search by IMDb or by title:
        $imdb = $movie['imdb_id'] ?? '';
if ($imdb && preg_match('/tt\d+/', $imdb)) {
    $tt_link = 'search.php?q=' . urlencode($imdb);
} else {
    $tt_link = '';
}

        echo '<div style="width: 110px; text-align: center;">';
        echo '<a href="' . $tt_link . '" target="_blank">';
        echo '<img src="' . ($movie['poster_path'] ? 'https://image.tmdb.org/t/p/w200' . $movie['poster_path'] : 'images/no-poster.png') . '" style="width:80px; height:120px; object-fit:cover; border-radius:2px;"><br>';
        echo '<span style="font-size:13px">' . htmlspecialchars($movie['title'] ?? $movie['original_title']) . '</span>';
        if (!empty($movie['release_date'])) {
            echo '<br><span style="font-size:11px; color:#888">' . htmlspecialchars(substr($movie['release_date'], 0, 4)) . '</span>';
        }
        echo '</a></div>';
    }
    echo '</div><br>';
}
?>

    <!-- סרטים דומים -->
    <hr>
    <h3>🎬 סרטים דומים:</h3>
    <?php if ($similar): ?>
      <div style="display:flex; flex-wrap:wrap; gap:16px;">
        <?php foreach ($similar as $sim): ?>
          <?php $sim_img = (!empty($sim['image_url'])) ? $sim['image_url'] : 'images/no-poster.png'; ?>
          <div style="width:100px; text-align:center;">
            <form method="post">
              <a href="poster.php?id=<?= $sim['id'] ?>">
                <img src="<?= htmlspecialchars($sim_img) ?>" style="width:100px; border-radius:1px;"><br>
                <small><?= htmlspecialchars($sim['title_en']) ?></small>
              </a><br>
              <button type="submit" name="remove_similar" value="<?= $sim['id'] ?>">🗑️</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p style="color:#888;">אין סרטים דומים כרגע</p>
    <?php endif; ?>

    <!-- ➕ טופס סרט דומה -->
    <h3>➕ הוסף סרט דומה</h3>
    <form method="post">
      <input type="text" name="similar_input" placeholder="מזהה פנימי, tt1234567 או קישור" required>
      <button type="submit" name="add_similar">📥 קישור</button>
    </form>
    <!-- 🎛 פעולות מערכת -->
<div class="actions" style="margin-top:20px;">
      <a href="edit.php?id=<?= $row['id'] ?>">✏️ ערוך</a> |
      <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('למחוק את הפוסטר?')">🗑️ מחק</a> |
      <a href="index.php">⬅ חזרה</a>
    </div>

  </div>
</div>
</body>
</html>
<?php
$conn->close();
include 'footer.php';
?>
