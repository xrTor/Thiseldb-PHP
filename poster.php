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
  return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
function format_imdb_rating($v) {
    return ($v !== null && $v !== '') ? number_format((float)$v, 1) : '—';
}

require_once 'server.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$view = $_GET['view'] ?? 'classic';

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
include 'languages.php';
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($display_title_en) ?></title>
  <link rel="stylesheet" href="style.css">
  <style>
    body.rtl { direction: rtl; font-family: Arial; background:#f1f1f1; padding:40px; }
    .poster-page { max-width:900px; margin:auto; background:#fff; padding:20px; border-radius:6px; box-shadow:0 0 6px rgba(0,0,0,0.1);}
    .view-toggle { margin-bottom: 24px; text-align: left;}
    .view-toggle a {
      background: #edf6ff; border-radius: 5px; padding: 7px 14px; color: #015;
      border: 1px solid #acd; margin-left: 5px; font-weight: bold; text-decoration: none;
    }
    .view-toggle a.selected { background: #007ee0; color: #fff; }
    /* טבלת מצב טבלה */
    .poster-table-main { display: flex; gap: 32px; }
    .poster-table-right { width: 240px; min-width: 180px; }
    .poster-table-img { width: 100%; max-width: 220px; border-radius:4px; }
    .poster-table-likes { margin: 16px 0 8px 0; text-align:center; }
    .poster-table-tags { margin-top:8px; }
    .tag { background:#eee; padding:4px 12px; margin:3px; display:inline-block; border-radius:12px; font-size:13px; color:#333; text-decoration:none; }
    .like-button { cursor: pointer; border:none; border-radius:6px; font-size:16px; padding:7px 18px; margin:0 2px 6px 2px; }
    .like-yes { background:#28a745; color:#fff;}
    .like-no { background:#dc3545; color:#fff;}
    .like-remove { background:#555; color:#fff;}
    .poster-table-list { width:100%; border-collapse:collapse; }
    .poster-table-list th { width: 160px; background: #eaf4ff; border-bottom:1px solid #d4e5f5; text-align:right; padding:8px; }
    .poster-table-list td { border-bottom:1px solid #eee; padding:8px; text-align:right; background:#fafcff; }
    .poster-table-actions { margin:18px 0 0 0; text-align:center;}
  </style>
</head>
<body class="rtl">
<div class="poster-page">
  <div class="view-toggle">
    <a href="?id=<?= $id ?>&view=classic" class="<?= ($view=='classic'?'selected':'') ?>">תצוגה רגילה</a>
    <a href="?id=<?= $id ?>&view=tbl" class="<?= ($view=='tbl'?'selected':'') ?>">תצוגת טבלה</a>
  </div>

<?php if ($view == 'tbl'): ?>
  <!-- מצב טבלה בלבד! -->
  <div style="text-align:left; margin-bottom:10px;">
    <a href="report.php?poster_id=<?= $row['id'] ?>" style="background:#ffdddd; color:#a00; padding:6px 12px; border-radius:6px; font-weight:bold; text-decoration:none;">🚨 דווח על תקלה בפוסטר</a>
  </div>
  <div class="poster-table-main">
    <div class="poster-table-right">
      <img src="<?= htmlspecialchars($row['image_url'] ?: 'images/no-poster.png') ?>" class="poster-table-img">
      <div class="poster-table-likes">
        <form method="post" style="display:inline;">
          <button type="submit" name="vote" value="like" class="like-button <?= ($user_vote=='like'?'like-yes':'') ?>">❤️ אהבתי (<?= $likes ?>)</button>
        </form>
        <form method="post" style="display:inline;">
          <button type="submit" name="vote" value="dislike" class="like-button <?= ($user_vote=='dislike'?'like-no':'') ?>">💔 לא אהבתי (<?= $dislikes ?>)</button>
        </form>
        <?php if ($user_vote): ?>
          <form method="post" style="display:inline;">
            <button type="submit" name="vote" value="remove" class="like-button like-remove">❌ בטל הצבעה</button>
          </form>
        <?php endif; ?>
      </div>
      <div class="poster-table-tags">
        <div style="margin-bottom:6px;"><strong>🗓️ שנה:</strong> <a href="home.php?year=<?=urlencode($row['year'])?>"><?= safe($row['year']) ?></a></div>
        <?php
          // דגלי שפה
          $lang_result = $conn->query("SELECT lang_code FROM poster_languages WHERE poster_id = $id");
          if ($lang_result->num_rows > 0) {
            echo '<div><strong>דגלי שפה:</strong><br>';
            while ($l = $lang_result->fetch_assoc()) {
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
            }
            echo '</div>';
          }
          // ז'אנרים
          if (!empty($row['genre'])) {
            echo '<div><strong>🎭 ז׳אנר:</strong> ';
            foreach (explode(',', $row['genre']) as $g) {
              $g_clean = trim($g);
              echo '<a class="tag" href="genre.php?name='.urlencode($g_clean).'">'.safe($g_clean).'</a> ';
            }
            echo '</div>';
          }
          // תגיות משתמש
          $res_user = $conn->query("SELECT id, genre FROM user_tags WHERE poster_id = $id");
          if ($res_user->num_rows > 0) {
            echo '<div style="margin-top:8px;"><strong>📝 תגיות משתמש:</strong><br>';
            while ($g = $res_user->fetch_assoc()) {
              $g_clean = trim($g['genre']);
              echo '<form method="post" style="display:inline;"><a href="user_tags.php?name='.urlencode($g_clean).'" class="tag">'.safe($g_clean).'</a>
              <button type="submit" name="remove_user_tags" value="'.$g['id'].'" style="border:none; background:none; color:#900; cursor:pointer;">🗑️</button>
              </form> ';
            }
            echo '</div>';
          }
        ?>
        <form method="post" style="margin-top:8px;">
          <input type="text" name="user_tags" placeholder="הוסף תגית" required style="width:110px;">
          <button type="submit" name="add_user_tags" class="tag" style="background:#dfe;">➕ הוסף</button>
        </form>
      </div>
      <div class="poster-table-actions">
        <a href="edit.php?id=<?= $id ?>" class="tag" style="background:#def;">✏️ ערוך</a>
        <a href="delete.php?id=<?= $id ?>" class="tag" style="background:#fed;" onclick="return confirm('למחוק את הפוסטר?')">🗑️ מחק</a>
      </div>
    </div>
    <div style="flex:1;">
      <table class="poster-table-list">
        <tr><th>כותרת (EN)</th><td><?= safe($row['title_en']) ?></td></tr>
        <tr><th>כותרת (עברית)</th><td><?= safe($row['title_he']) ?></td></tr>
        <tr><th>רשת</th><td><?= !empty($row['network']) ? '<a href="network.php?name='.urlencode($row['network']).'">'.safe($row['network']).'</a>' : '' ?></td></tr>
        <tr><th>עונות</th><td><?= !empty($row['season_count']) ? '<a href="season.php?id='.$id.'">'.intval($row['season_count']).'</a>' : '' ?></td></tr>
        <tr><th>פרקים</th><td><?= !empty($row['episode_count']) ? '<a href="episode.php?id='.$id.'">'.intval($row['episode_count']).'</a>' : '' ?></td></tr>
        <tr><th>שפות</th><td>
          <?php
            $langs = array_filter(array_map('trim', explode(',', $row['languages'] ?? '')));
            foreach ($langs as $lang) {
              echo '<a class="tag" href="language_imdb.php?lang_code='.urlencode($lang).'">'.safe($lang).'</a> ';
            }
          ?>
        </td></tr>
        <tr><th>מדינות</th><td>
          <?php
            $countries = array_filter(array_map('trim', explode(',', $row['countries'] ?? '')));
            foreach ($countries as $country) {
              $disp = str_replace(['United States','United Kingdom'],['USA','UK'],$country);
              echo '<a class="tag" href="country.php?country='.urlencode($country).'">'.safe($disp).'</a> ';
            }
          ?>
        </td></tr>
        <tr><th>במאים</th><td>
          <?php foreach (explode(',', $row['directors']) as $val) { $val=trim($val); if($val) echo '<a href="actor.php?name='.urlencode($val).'" class="tag">'.safe($val).'</a> ';} ?>
        </td></tr>
        <tr><th>שחקנים</th><td>
          <?php foreach (explode(',', $row['actors']) as $val) { $val=trim($val); if($val) echo '<a href="actor.php?name='.urlencode($val).'" class="tag">'.safe($val).'</a> ';} ?>
        </td></tr>
        <tr><th>תסריטאים</th><td>
          <?php foreach (explode(',', $row['writers']) as $val) { $val=trim($val); if($val) echo '<a href="actor.php?name='.urlencode($val).'" class="tag">'.safe($val).'</a> ';} ?>
        </td></tr>
        <tr><th>מפיקים</th><td>
          <?php foreach (explode(',', $row['producers']) as $val) { $val=trim($val); if($val) echo '<a href="actor.php?name='.urlencode($val).'" class="tag">'.safe($val).'</a> ';} ?>
        </td></tr>
        <tr><th>צלמים</th><td>
          <?php foreach (explode(',', $row['cinematographers']) as $val) { $val=trim($val); if($val) echo '<a href="actor.php?name='.urlencode($val).'" class="tag">'.safe($val).'</a> ';} ?>
        </td></tr>
        <tr><th>מלחינים</th><td>
          <?php foreach (explode(',', $row['composers']) as $val) { $val=trim($val); if($val) echo '<a href="actor.php?name='.urlencode($val).'" class="tag">'.safe($val).'</a> ';} ?>
        </td></tr>
        <tr><th>IMDb</th><td>
          <a href="https://www.imdb.com/title/<?= safe($row['imdb_id']) ?>" class="tag" target="_blank"><?= safe($row['imdb_id']) ?></a>
        </td></tr>
        <tr><th>דירוג IMDb</th><td><?= format_imdb_rating($row['imdb_rating']) ?></td></tr>
        <tr><th>Metacritic</th><td><?= safe($row['metacritic_score']) ?></td></tr>
        <tr><th>Rotten Tomatoes</th><td><?= safe($row['rt_score']) ?></td></tr>
        <tr><th>משך זמן</th><td><?= pretty_runtime($row['runtime']) ?></td></tr>
        <tr><th>טריילר</th>
          <td>
            <?php if ($video_id): ?>
              <a href="https://www.youtube.com/watch?v=<?= safe($video_id) ?>" target="_blank">לצפייה</a>
            <?php else: ?>
              <span style="color:#888">אין</span>
            <?php endif; ?>
          </td>
        </tr>
        <tr><th>תקציר (עברית)</th><td><?= nl2br(safe($row['plot_he'])) ?></td></tr>
        <tr><th>תקציר (English)</th><td><?= nl2br(safe($row['plot'])) ?></td></tr>
      </table>
    </div>
  </div>
<?php else: ?>
  <!-- כל התצוגה הרגילה, בלי שינוי, כמו בקוד שלך -->
<?php
// 👇 כל קוד התצוגה הרגילה שלך כאן (ללא שינוי, כולל תמונה, כפתורים, עונות/פרקים, תגיות, TMDB, דומים וכו') 👇
?>

<!-- === כאן תדביק את הקוד של מצב רגיל ששלחת מקודם === -->
<?php
// כל הבלוק הארוך שלך (תצוגה רגילה עם כל הפרטים) נמצא כאן
?>

<?php endif; ?>
</div>
</body>
</html>
<?php
$conn->close();
include 'footer.php';
?>
