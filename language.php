<?php
include 'header.php';
 require_once 'server.php';
 

$lang_code = $_GET['lang_code'] ?? '';
$lang_code = trim($lang_code);

if (empty($lang_code)) {
    echo "<p style='text-align:center;'>❌ לא נבחרה שפה</p>";
    exit;
}

// שליפת פוסטרים לפי השפה
$stmt = $conn->prepare("
  SELECT posters.*
  FROM posters
  JOIN poster_languages ON posters.id = poster_languages.poster_id
  WHERE poster_languages.lang_code = ?
  ORDER BY posters.year DESC
");
$stmt->bind_param("s", $lang_code);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>פוסטרים בשפה <?= htmlspecialchars($lang_code) ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h2 style="text-align:center;">🎬 פוסטרים בשפה: <?= htmlspecialchars($lang_code) ?></h2>

  <?php if ($result->num_rows > 0): ?>
    <div style="display:flex; flex-wrap:wrap; justify-content:center;">
      <?php while ($row = $result->fetch_assoc()): ?>
        <div style="width:200px; margin:10px; text-align:center;">
          <a href="poster.php?id=<?= $row['id'] ?>">
            <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="Poster" style="width:100%; border-radius:6px;">
            <p><?= htmlspecialchars($row['title_en']) ?></p>
          </a>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <p style="text-align:center;">😢 לא נמצאו פוסטרים בשפה <?= htmlspecialchars($lang_code) ?></p>
  <?php endif; ?>

  <div style="text-align:center; margin-top:20px;">
    <a href="index.php">⬅ חזרה לרשימה</a>
  </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
include 'footer.php';
?>
