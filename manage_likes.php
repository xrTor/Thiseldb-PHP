<?php
$conn = new mysqli('localhost', 'root', '123456', 'media');
if ($conn->connect_error) die("Connection failed");

$message = '';

// מחיקת לייק לפי מזהה
if (isset($_GET['delete'])) {
  $did = intval($_GET['delete']);
  $conn->query("DELETE FROM poster_likes WHERE id = $did");
  $message = "🗑️ לייק נמחק";
}

// שליפת כל הלייקים
$result = $conn->query("
  SELECT pl.*, p.title_en 
  FROM poster_likes pl 
  JOIN posters p ON p.id = pl.poster_id 
  ORDER BY pl.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>ניהול לייקים לפוסטרים</title>
  <style>
    body { font-family: Arial; background:#f5f5f5; padding:40px; direction:rtl; }
    .like-box { background:#fff; padding:16px; margin-bottom:20px; border-radius:6px; box-shadow:0 0 4px rgba(0,0,0,0.1); }
    .info { font-size:14px; color:#666; margin-bottom:8px; }
    a.btn { padding:6px 12px; background:#eee; border-radius:6px; text-decoration:none; margin-right:10px; }
    a.btn:hover { background:#ddd; }
    .message { background:#ffe; border:1px solid #cc9; padding:10px; border-radius:6px; margin-bottom:20px; font-weight:bold; color:#444; }
  </style>
</head>
<body>

<h2>❤️ ניהול לייקים לפוסטרים</h2>

<?php if ($message): ?>
  <div class="message"><?= $message ?></div>
<?php endif; ?>

<?php while ($row = $result->fetch_assoc()): ?>
  <div class="like-box">
    <div class="info">
      🕓 <?= htmlspecialchars($row['created_at']) ?> |
      📄 פוסטר: <a href="poster.php?id=<?= $row['poster_id'] ?>" target="_blank"><?= htmlspecialchars($row['title_en']) ?></a> |
      🌐 IP: <?= htmlspecialchars($row['ip_address']) ?>
    </div>
    <a href="manage_likes.php?delete=<?= $row['id'] ?>" class="btn" onclick="return confirm('למחוק את הלייק?')">🗑️ מחק לייק זה</a>
  </div>
<?php endwhile; ?>

</body>
</html>

<?php $conn->close(); ?>
