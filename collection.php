<?php
$conn = new mysqli('localhost', 'root', '123456', 'media');
if ($conn->connect_error) die("Connection failed");

$name = isset($_GET['name']) ? trim($_GET['name']) : '';
if ($name === '') { echo "<p>❌ אוסף לא צוין</p>"; exit; }

// שליפת פרטי האוסף
$stmt = $conn->prepare("SELECT * FROM collections WHERE name = ?");
$stmt->bind_param("s", $name); $stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
  echo "<p>❌ האוסף לא נמצא</p>"; exit;
}
$collection = $result->fetch_assoc();
$stmt->close();

// שליפת פוסטרים באוסף
$cid = $collection['id'];
$poster_list = [];
$res = $conn->query("
  SELECT p.* FROM poster_collections pc
  JOIN posters p ON p.id = pc.poster_id
  WHERE pc.collection_id = $cid
");
while ($row = $res->fetch_assoc()) $poster_list[] = $row;
?>

<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>📦 אוסף: <?= htmlspecialchars($collection['name']) ?></title>
  <style>
    body { font-family: Arial; background:#f1f1f1; padding:40px; direction:rtl; }
    .collection-page { max-width:900px; margin:auto; background:#fff; padding:20px; border-radius:6px; }
    .poster-grid { display:flex; flex-wrap:wrap; gap:16px; margin-top:20px; }
    .poster-item { width:120px; text-align:center; }
    .poster-item img { width:100%; border-radius:6px; box-shadow:0 0 4px rgba(0,0,0,0.1); }
    .description { margin-top:10px; color:#444; }
  </style>
</head>
<body>

<div class="collection-page">
  <h2>📦 אוסף: <?= htmlspecialchars($collection['name']) ?></h2>

  <?php if (!empty($collection['image_url'])): ?>
    <img src="<?= htmlspecialchars($collection['image_url']) ?>" alt="Collection" style="max-width:100%; border-radius:6px;">
  <?php endif; ?>

  <?php if (!empty($collection['description'])): ?>
    <div class="description"><?= nl2br(htmlspecialchars($collection['description'])) ?></div>
  <?php endif; ?>

  <h3>🎬 סרטים באוסף זה:</h3>
  <?php if ($poster_list): ?>
    <div class="poster-grid">
      <?php foreach ($poster_list as $p): ?>
        <div class="poster-item">
          <a href="poster.php?id=<?= $p['id'] ?>">
            <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="Poster">
            <div><small><?= htmlspecialchars($p['title_en']) ?></small></div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p style="color:#888;">אין פוסטרים באוסף זה</p>
  <?php endif; ?>
</div>

</body>
</html>

<?php $conn->close(); ?>
