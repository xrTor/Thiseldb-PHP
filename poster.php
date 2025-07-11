<?php
include 'header.php';

function extractYoutubeId($url) {
  if (preg_match('/(?:v=|\/embed\/|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches))
    return $matches[1];
  return '';
}

function extractImdbId($input) {
  if (preg_match('/tt\d{7,8}/', $input, $matches))
    return $matches[0];
  return '';
}

function extractLocalId($input) {
  if (preg_match('/poster\.php\?id=(\d+)/', $input, $matches))
    return (int)$matches[1];
  return 0;
}

$conn = new mysqli('localhost', 'root', '123456', 'media');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$result = $conn->query("SELECT * FROM posters WHERE id = $id");
if ($result->num_rows == 0) {
  echo "<p style='text-align:center;'>😢 פוסטר לא נמצא</p>";
  exit;
}
$row = $result->fetch_assoc();
$languages = include 'languages.php';
$message = '';

// 🎬 הוספת סרט דומה עם קלט חכם
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_similar'])) {
  $input = trim($_POST['similar_input'] ?? '');
  $target_id = 0;

  if (is_numeric($input)) {
    $target_id = (int)$input;
  } elseif ($local = extractLocalId($input)) {
    $target_id = $local;
  } elseif ($imdb = extractImdbId($input)) {
    $stmt = $conn->prepare("SELECT id FROM posters WHERE imdb_id = ?");
    $stmt->bind_param("s", $imdb);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($r = $res->fetch_assoc()) $target_id = $r['id'];
    $stmt->close();
  }

  if ($target_id > 0 && $target_id != $id) {
    $check = $conn->prepare("SELECT 1 FROM poster_similar WHERE poster_id = ? AND similar_id = ?");
    $check->bind_param("ii", $id, $target_id);
    $check->execute(); $check->store_result();

    if ($check->num_rows == 0) {
      $insert = $conn->prepare("INSERT INTO poster_similar (poster_id, similar_id) VALUES (?, ?)");
      $insert->bind_param("ii", $id, $target_id);
      $insert->execute(); $insert->close();

      // קשר הפוך
      $reverse = $conn->prepare("SELECT 1 FROM poster_similar WHERE poster_id = ? AND similar_id = ?");
      $reverse->bind_param("ii", $target_id, $id);
      $reverse->execute(); $reverse->store_result();
      if ($reverse->num_rows == 0) {
        $add_back = $conn->prepare("INSERT INTO poster_similar (poster_id, similar_id) VALUES (?, ?)");
        $add_back->bind_param("ii", $target_id, $id);
        $add_back->execute(); $add_back->close();
      }
      $reverse->close();

      $message = "✅ הקשר נוצר בהצלחה (דו־כיווני)";
    } else {
      $message = "⚠️ הקשר כבר קיים";
    }
    $check->close();
  } else {
    $message = "❌ לא נמצא סרט מתאים עם קלט זה";
  }
}

// 🗑️ מחיקה דו־כיוונית
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['remove_similar'])) {
  $remove_id = (int)$_POST['remove_similar'];
  $del1 = $conn->prepare("DELETE FROM poster_similar WHERE poster_id = ? AND similar_id = ?");
  $del1->bind_param("ii", $id, $remove_id);
  $del1->execute(); $del1->close();

  $del2 = $conn->prepare("DELETE FROM poster_similar WHERE poster_id = ? AND similar_id = ?");
  $del2->bind_param("ii", $remove_id, $id);
  $del2->execute(); $del2->close();

  $message = "🗑️ הקשר הוסר בהצלחה";
}

// סרטים דומים
$stmt = $conn->prepare("SELECT p.* FROM poster_similar ps JOIN posters p ON p.id = ps.similar_id WHERE ps.poster_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$similar = [];
while ($r = $res->fetch_assoc()) $similar[] = $r;
$stmt->close();
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($row['title_en']) ?></title>
  <link rel="stylesheet" href="style.css">
  <style>
    body.rtl { direction: rtl; font-family: Arial; }
    .poster-page { max-width:800px; margin:30px auto; background:#f9f9f9 url('wbg.png'); padding:20px; border-radius:6px; box-shadow:0 0 8px rgba(0,0,0,0.1); }
    .poster-image { width:200px; float:right; margin-left:20px; border-radius:6px; box-shadow:0 0 4px rgba(0,0,0,0.08); }
    .poster-details { overflow:hidden; }
    .tag { background:#eee; padding:6px 12px; margin:4px; display:inline-block; border-radius:12px; font-size:13px; }
    .similar-grid { display:flex; flex-wrap:wrap; gap:14px; margin-top:10px; }
    .similar-item { width:100px; text-align:center; }
    .similar-item img { width:100px; border-radius:6px; box-shadow:0 0 6px rgba(0,0,0,0.05); }
    .actions a { margin:0 10px; color:#007bff; text-decoration:none; }
    .actions a:hover { text-decoration:underline; }
  </style>
</head>
<body class="rtl">

<div class="poster-page">

  <!-- 🚨 כפתור דיווח על תקלה בראש העמוד -->
  <div style="text-align:left; margin-bottom:10px;">
    <a href="report.php?poster_id=<?= $row['id'] ?>" style="
      display:inline-block;
      background:#ffdddd;
      color:#a00;
      padding:6px 12px;
      border-radius:6px;
      font-weight:bold;
      text-decoration:none;
    ">🚨 דווח על תקלה בפוסטר</a>
  </div>

  <?php if ($message): ?>
    <p style="background:#ffe; border:1px solid #cc9; padding:10px; border-radius:6px; color:#444; font-weight:bold;">
      <?= $message ?>
    </p>
  <?php endif; ?>

  <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="Poster" class="poster-image">
  <div class="poster-details">
    <h2>
      <?= htmlspecialchars($row['title_en']) ?>
      <?php if (!empty($row['title_he'])): ?><br><?= htmlspecialchars($row['title_he']) ?><?php endif; ?>
    </h2>

    <p><strong>🗓️ שנה:</strong> <?= htmlspecialchars($row['year']) ?></p>
    <p><strong>🎞️ סוג:</strong> <?= $row['type'] === 'series' ? 'סדרה' : 'סרט' ?></p>
    <p><strong>⭐ IMDb:</strong> <?= $row['imdb_rating'] ? htmlspecialchars($row['imdb_rating']) . ' / 10' : 'לא זמין' ?></p>
    <p><strong>🔤 IMDb ID:</strong> <?= htmlspecialchars($row['imdb_id']) ?></p>

    <?php if ($row['genre']):
      $genres = explode(',', $row['genre']);
      echo "<p><strong>🎭 ז'אנר:</strong><br>";
      foreach ($genres as $g) {
        $g_clean = trim($g);
        echo "<a href='genre.php?name=" . urlencode($g_clean) . "' class='tag'>" . htmlspecialchars($g_clean) . "</a> ";
      }
      echo "</p>";
    endif; ?>

    <?php if ($row['actors']):
      $actors = explode(',', $row['actors']);
      echo "<p><strong>👥 שחקנים:</strong><br>";
      foreach ($actors as $a) {
        $a_clean = trim($a);
        echo "<a href='actor.php?name=" . urlencode($a_clean) . "' class='tag'>" . htmlspecialchars($a_clean) . "</a> ";
      }
      echo "</p>";
    endif; ?>

    <!-- 🎬 סרטים דומים -->
    <hr>
    <h3>🎬 סרטים דומים:</h3>
    <?php if ($similar): ?>
      <div class="similar-grid">
        <?php foreach ($similar as $sim): ?>
          <div class="similar-item">
            <form method="post">
              <a href="poster.php?id=<?= $sim['id'] ?>">
                <img src="<?= htmlspecialchars($sim['image_url']) ?>" alt="Poster">
                <div><small><?= htmlspecialchars($sim['title_en']) ?></small></div>
              </a>
              <button type="submit" name="remove_similar" value="<?= $sim['id'] ?>">🗑️</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p style="color:#888;">אין סרטים דומים כרגע</p>
    <?php endif; ?>

    <!-- ➕ טופס הוספה חכם -->
    <h3>➕ הוסף סרט דומה</h3>
    <form method="post">
      <input type="text" name="similar_input" placeholder="מזהה פנימי, tt1234567 או קישור IMDb/פוסטר">
      <button type="submit" name="add_similar">📥 קישור</button>
    </form>

    <!-- 🔧 פעולות -->
    <div class="actions">
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
