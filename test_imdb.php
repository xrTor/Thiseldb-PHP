<?php
$conn = new mysqli('localhost', 'root', '123456', 'media');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$result = $conn->query("SELECT * FROM posters ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>🎬 כל הפוסטרים</title>
  <style>
    body {
      font-family: Arial;
      background: #f5f5f5;
      padding: 40px;
      text-align: center;
    }
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
      margin-top: 40px;
    }
    .card {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.07);
      text-align: right;
      direction: rtl;
    }
    .card img {
      max-width: 100%;
      border-radius: 8px;
      margin-bottom: 10px;
    }
    .card h3 {
      margin: 0 0 10px;
    }
    .card p {
      margin: 5px 0;
      font-size: 14px;
    }
    .card a {
      color: #2196F3;
      text-decoration: none;
    }
  </style>
</head>
<body>

<h2>📋 כל הפוסטרים שנשמרו</h2>

<div class="grid">
<?php while ($row = $result->fetch_assoc()): ?>
  <div class="card">
    <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="פוסטר">
    <h3><?= htmlspecialchars($row['title_en']) ?></h3>
    <p><strong>דירוג:</strong> <?= htmlspecialchars($row['imdb_rating']) ?></p>
    <p><strong>עלילה:</strong><br><?= nl2br(htmlspecialchars($row['plot'])) ?></p>
    <p><a href="<?= htmlspecialchars($row['imdb_link']) ?>" target="_blank">🔗 IMDb</a></p>
  </div>
<?php endwhile; ?>
</div>

</body>
</html>
