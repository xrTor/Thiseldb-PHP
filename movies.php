<?php
include 'bar.php';

$conn = new mysqli('localhost','root','123456','media');
if ($conn->connect_error) die("Connection failed");

$sql = "SELECT * FROM posters WHERE type = 'movie' ORDER BY year DESC";
$res = $conn->query($sql);
$rows = $res->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>🎬 סרטים | Thiseldb</title>
  <style>
    body { font-family: sans-serif; background:#f9f9f9; }
    h1 { text-align:center; margin-top:30px; }
    .poster-wall { display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; margin: 30px 0; }
    .poster {
      width: 200px;
      background: #fff;
      border: 1px solid #ddd;
      padding: 10px;
      box-shadow: 0 0 5px rgba(0,0,0,0.05);
      text-align: center;
      border-radius: 6px;
    }
    .poster img { width: 100%; border-radius: 4px; }
    .lang-icon { margin-top: 4px; font-size: 18px; }
    .rating { font-size: 14px; margin-top:6px; color:#666; }
    .details-link { margin-top:8px; display:inline-block; color:#007bff; text-decoration:none; font-size:13px; }
    .details-link:hover { text-decoration:underline; }
  </style>
</head>
<body>

<h1>🎬 סרטים רגילים</h1>

<div class="poster-wall">
  <?php if (empty($rows)): ?>
    <p>😢 לא נמצאו פוסטרים</p>
  <?php else: ?>
    <?php foreach ($rows as $row): ?>
      <div class="poster">
        <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="">
        <div><strong><?= htmlspecialchars($row['title_en']) ?></strong></div>
        <div>🗓️ <?= htmlspecialchars($row['year']) ?></div>

        <div class="lang-icon">
          <?php
          $lang_icons = [
            'en'=>'🇬🇧','he'=>'🇮🇱','fr'=>'🇫🇷','es'=>'🇪🇸',
            'ja'=>'🇯🇵','de'=>'🇩🇪','zh'=>'🇨🇳','ko'=>'🇰🇷'
          ];
          $lang = $row['lang_code'] ?? '';
          echo $lang_icons[$lang] ?? '🌐';
          ?>
        </div>

        <div class="rating">
          ⭐ <?= $row['imdb_rating'] ?? '—' ?> / 10
        </div>

        <a class="details-link" href="poster.php?id=<?= $row['id'] ?>">📄 לפרטים</a>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<p style="text-align:center; margin-top:30px;">
  <a href="index.php">⬅ חזרה לכל הפוסטרים</a>
</p>

</body>
</html>

<?php $conn->close(); ?>
<?php include 'footer.php'; ?>
