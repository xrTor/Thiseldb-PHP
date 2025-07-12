<?php
session_start();
include 'header.php';

$conn = new mysqli('localhost', 'root', '123456', 'media');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// הגדרות תצוגה
$allowed_limits = [5, 10, 20, 50, 100, 250];
$limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], $allowed_limits) ? (int)$_GET['limit'] : 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// ספירת פוסטרים
$total_rows = $conn->query("SELECT COUNT(*) AS c FROM posters")->fetch_assoc()['c'];
$total_pages = ($limit > 0) ? max(1, ceil($total_rows / $limit)) : 1;

// שליפת פוסטרים לפי ID
$sql = "SELECT * FROM posters ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// בדיקת הצלחה
if (!$result) {
  echo "<div style='color:#a00; background:#fee; padding:10px; border-radius:6px;'>❌ שגיאה בשאילתה: " . $conn->error . "</div>";
}
?>

<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>🎬 פוסטרים - עמוד <?= $page ?></title>
  <link rel="stylesheet" href="style.css">
  <style>
    body.rtl { direction: rtl; font-family: Arial; background:#f8f8f8; padding:40px; }
    .poster-grid {
      display: flex; flex-wrap: wrap; gap:20px; justify-content: center;
    }
    .poster-card {
      width:180px; background:#fff; border-radius:6px;
      box-shadow: 0 0 6px rgba(0,0,0,0.1); padding:10px; text-align:center;
    }
    .poster-card img {
      width:100%; border-radius:6px; box-shadow: 0 0 4px rgba(0,0,0,0.08);
    }
    .pagination {
      margin-top:30px; font-weight:bold;
    }
    .empty-message {
      background:#ffe; border:1px solid #cc9;
      padding:12px; border-radius:6px;
      color:#555; text-align:center;
      margin:20px auto; max-width:500px;
    }
  </style>
</head>
<body class="rtl">

<h2>🎬 פוסטרים אחרונים</h2>

<?php if ($result && $result->num_rows > 0): ?>
  <div class="poster-grid">
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="poster-card">
        <a href="poster.php?id=<?= $row['id'] ?>">
          <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="<?= htmlspecialchars($row['title_en']) ?>">
          <div><?= htmlspecialchars($row['title_en']) ?></div>
        </a>
      </div>
    <?php endwhile; ?>
  </div>
<?php else: ?>
  <div class="empty-message">😢 לא נמצאו פוסטרים להצגה</div>
<?php endif; ?>

<div class="pagination">
  עמוד <?= $page ?> מתוך <?= $total_pages ?>
  <?php if ($page > 1): ?>
    | <a href="index.php?page=<?= $page - 1 ?>&limit=<?= $limit ?>">⬅ קודם</a>
  <?php endif; ?>
  <?php if ($page < $total_pages): ?>
    | <a href="index.php?page=<?= $page + 1 ?>&limit=<?= $limit ?>">➡ הבא</a>
  <?php endif; ?>
</div>

</body>
</html>

<?php
$conn->close();
include 'footer.php';
?>
