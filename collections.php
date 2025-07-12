<?php
include 'header.php';

$conn = new mysqli('localhost', 'root', '123456', 'media');
if ($conn->connect_error) die("Connection failed");

$message = '';

// מחיקת אוסף
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_collection'])) {
  $cid = (int)$_POST['delete_collection'];
  $conn->query("DELETE FROM collections WHERE id = $cid");
  $conn->query("DELETE FROM collection_items WHERE collection_id = $cid");
  $message = "🗑️ האוסף נמחק בהצלחה";
}

$res = $conn->query("SELECT c.*, COUNT(ci.id) as total_items
  FROM collections c
  LEFT JOIN collection_items ci ON c.id = ci.collection_id
  GROUP BY c.id
  ORDER BY c.created_at DESC");
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>📁 אוספים</title>
  <style>
    body { font-family:Arial; direction:rtl; background:#f9f9f9; padding:40px; }
    .collection-card {
      background:white;
      padding:20px;
      margin:10px auto;
      border-radius:6px;
      box-shadow:0 0 4px rgba(0,0,0,0.1);
      max-width:600px;
      position:relative;
    }
    .collection-card h3 {
      margin:0 0 10px 0;
      font-size:20px;
    }
    .collection-card .description {
      color:#555;
      margin-bottom:10px;
    }
    .collection-card .count {
      color:#999;
      font-size:14px;
    }
    .collection-card .actions {
      position:absolute;
      top:20px;
      left:20px;
    }
    .collection-card .actions a, .collection-card .actions button {
      margin-right:6px;
      text-decoration:none;
      font-size:14px;
      background:none;
      border:none;
      color:#007bff;
      cursor:pointer;
    }
    .collection-card .actions button:hover,
    .collection-card .actions a:hover {
      text-decoration:underline;
    }
    .message {
      background:#ffe; padding:10px; border-radius:6px; margin-bottom:10px; border:1px solid #ddc; color:#333;
      max-width:600px; margin:auto;
    }
    .add-new {
      text-align:center; margin-top:30px;
    }
    .add-new a {
      background:#007bff; color:white; padding:10px 20px; border-radius:6px; text-decoration:none;
    }
  </style>
</head>
<body>

<h2 style="text-align:center;">📁 רשימת האוספים</h2>

<?php if ($message): ?>
  <div class="message"><?= $message ?></div>
<?php endif; ?>

<?php if ($res->num_rows > 0): ?>
  <?php while ($c = $res->fetch_assoc()): ?>
    <div class="collection-card">
      <h3><a href="collections.php?id=<?= $c['id'] ?>">📁 <?= htmlspecialchars($c['name']) ?></a></h3>
      <div class="description"><?= htmlspecialchars($c['description']) ?></div>
      <div class="count">🎞️ <?= $c['total_items'] ?> פוסטרים</div>
      <div class="actions">
        <a href="edit_collection.php?id=<?= $c['id'] ?>">✏️ ערוך</a>
        <form method="post" style="display:inline;">
          <button type="submit" name="delete_collection" value="<?= $c['id'] ?>" onclick="return confirm('למחוק את האוסף?')">🗑️ מחק</button>
        </form>
        <a href="add_to_collection.php?id=<?= $c['id'] ?>">➕ הוסף פוסטר</a>
      </div>
    </div>
  <?php endwhile; ?>
<?php else: ?>
  <p style="text-align:center;">😢 לא קיימים אוספים כרגע</p>
<?php endif; ?>

<div class="add-new">
  <a href="create_collection.php">➕ צור אוסף חדש</a>
</div>

</body>
</html>

<?php include 'footer.php'; ?>
