<?php
$conn = new mysqli('localhost', 'root', '123456', 'media');
if ($conn->connect_error) die("Connection failed");

$message = '';

// יצירת אוסף חדש
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['create_collection'])) {
  $name = trim($_POST['name'] ?? '');
  $desc = trim($_POST['description'] ?? '');
  $img = trim($_POST['image_url'] ?? '');

  if ($name !== '') {
    $stmt = $conn->prepare("INSERT INTO collections (name, description, image_url) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $desc, $img); $stmt->execute(); $stmt->close();
    $message = "✅ האוסף נוצר בהצלחה";
  } else {
    $message = "⚠️ שם האוסף הוא חובה";
  }
}

// מחיקה
if (isset($_GET['delete'])) {
  $did = intval($_GET['delete']);
  $conn->query("DELETE FROM collections WHERE id = $did");
  $message = "🗑️ אוסף נמחק";
}

// הודעות מעבר
if (isset($_GET['msg'])) {
  if ($_GET['msg'] === 'linked') $message = "📥 הפוסטר קושר לאוסף";
  elseif ($_GET['msg'] === 'exists') $message = "⚠️ הפוסטר כבר מקושר";
  elseif ($_GET['msg'] === 'error') $message = "❌ שגיאה בקישור";
}

// שליפת כל האוספים
$result = $conn->query("SELECT * FROM collections ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>ניהול אוספים</title>
  <style>
    body { font-family: Arial; background:#f6f6f6; padding:40px; direction:rtl; }
    .collection { border:1px solid #ccc; padding:16px; background:#fff; margin-bottom:20px; border-radius:6px; }
    .collection img { max-width:200px; border-radius:6px; margin-bottom:10px; }
    .message { background:#ffe; border:1px solid #cc9; padding:10px; border-radius:6px; color:#444; font-weight:bold; margin-bottom:20px; }
    form input, form textarea { width:100%; padding:8px; margin-bottom:10px; border-radius:6px; border:1px solid #ccc; }
    form button { padding:10px 20px; border:none; border-radius:6px; background:#007bff; color:#fff; cursor:pointer; }
    form button:hover { background:#0056b3; }
    .link-btn { background:#eee; padding:6px 12px; border-radius:6px; text-decoration:none; margin-right:10px; }
    .link-btn:hover { background:#ddd; }
  </style>
</head>
<body>

<h2>📦 ניהול אוספים</h2>

<?php if ($message): ?>
  <div class="message"><?= $message ?></div>
<?php endif; ?>

<h3>➕ צור אוסף חדש</h3>
<form method="post">
  <input type="text" name="name" placeholder="שם האוסף" required>
  <textarea name="description" placeholder="תיאור האוסף"></textarea>
  <input type="text" name="image_url" placeholder="תמונה (URL)">
  <button type="submit" name="create_collection">📦 צור</button>
</form>

<h3>📚 כל האוספים</h3>
<?php while ($row = $result->fetch_assoc()): ?>
  <div class="collection">
    <?php if (!empty($row['image_url'])): ?>
      <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="Collection">
    <?php endif; ?>
    <h4><?= htmlspecialchars($row['name']) ?></h4>
    <p><?= nl2br(htmlspecialchars($row['description'])) ?></p>
    <small>🕓 נוצר ב־<?= htmlspecialchars($row['created_at']) ?></small><br><br>
    <a href="edit_collection.php?id=<?= $row['id'] ?>" class="link-btn">✏️ ערוך</a>
    <a href="manage_collections.php?delete=<?= $row['id'] ?>" onclick="return confirm('למחוק את האוסף?')" class="link-btn">🗑️ מחק</a>
    <a href="collection.php?name=<?= urlencode($row['name']) ?>" class="link-btn">📦 צפייה ציבורית</a>

    <!-- 📥 טופס קישור פוסטר -->
    <form method="post" action="add_to_collection.php" style="margin-top:10px;">
      <input type="hidden" name="collection_id" value="<?= $row['id'] ?>">
      <input type="number" name="poster_id" placeholder="מזהה פוסטר לקישור" required>
      <button type="submit">📥 קישור פוסטר</button>
    </form>
  </div>
<?php endwhile; ?>

</body>
</html>

<?php $conn->close(); ?>
