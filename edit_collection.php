<?php
$conn = new mysqli('localhost', 'root', '123456', 'media');
if ($conn->connect_error) die("Connection failed");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';

// שליפת נתוני האוסף
$stmt = $conn->prepare("SELECT * FROM collections WHERE id = ?");
$stmt->bind_param("i", $id); $stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
  echo "<p>❌ אוסף לא נמצא</p>"; exit;
}
$collection = $result->fetch_assoc();
$stmt->close();

// עדכון נתונים
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_collection'])) {
  $name = trim($_POST['name'] ?? '');
  $desc = trim($_POST['description'] ?? '');
  $img = trim($_POST['image_url'] ?? '');

  if ($name !== '') {
    $stmt = $conn->prepare("UPDATE collections SET name=?, description=?, image_url=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $desc, $img, $id); $stmt->execute(); $stmt->close();
    $message = "✅ האוסף עודכן בהצלחה";

    // טען מחדש
    $res = $conn->query("SELECT * FROM collections WHERE id = $id");
    $collection = $res->fetch_assoc();
  } else {
    $message = "⚠️ שם האוסף הוא חובה";
  }
}
?>

<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>עריכת אוסף</title>
  <style>
    body { font-family: Arial; background:#f6f6f6; padding:40px; direction:rtl; }
    .message { background:#ffe; border:1px solid #cc9; padding:10px; border-radius:6px; color:#444; font-weight:bold; margin-bottom:20px; }
    input, textarea { width:100%; padding:8px; margin-bottom:10px; border-radius:6px; border:1px solid #ccc; }
    button { padding:10px 20px; border:none; border-radius:6px; background:#007bff; color:#fff; cursor:pointer; }
    button:hover { background:#0056b3; }
  </style>
</head>
<body>

<h2>✏️ עריכת אוסף: <?= htmlspecialchars($collection['name']) ?></h2>

<?php if ($message): ?>
  <div class="message"><?= $message ?></div>
<?php endif; ?>

<form method="post">
  <input type="text" name="name" value="<?= htmlspecialchars($collection['name']) ?>" required>
  <textarea name="description"><?= htmlspecialchars($collection['description']) ?></textarea>
  <input type="text" name="image_url" value="<?= htmlspecialchars($collection['image_url']) ?>">
  <button type="submit" name="update_collection">💾 שמור שינויים</button>
</form>

<a href="manage_collections.php">⬅ חזרה לניהול</a>

</body>
</html>

<?php $conn->close(); ?>
