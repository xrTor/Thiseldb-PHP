<?php
$host = 'localhost';
$db = 'media';
$user = 'root';
$pass = '123456';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$result = $conn->query("SELECT * FROM posters WHERE id = $id");

if ($result->num_rows == 0) {
    echo "<p style='text-align:center;'>😢 פוסטר לא נמצא</p>";
    exit;
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($row['title_en']) ?></title>
  <link rel="stylesheet" href="style.css">
  <style>
    .poster-page {
      max-width: 800px;
      margin: 30px auto;
      background: #fff;
      padding: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      border-radius: 10px;
    }
    .poster-image {
      width: 200px;
      float: left;
      margin-left: 20px;
      border-radius: 6px;
    }
    .poster-details {
      overflow: hidden;
    }
    .poster-tags {
      margin-top: 10px;
    }
    .tag {
      background: #eee;
      padding: 5px 10px;
      margin: 5px;
      display: inline-block;
      border-radius: 4px;
      font-size: 13px;
    }
    .actions {
      margin-top: 20px;
      text-align: center;
    }
    .actions a {
      color: #2d89ef;
      text-decoration: none;
      margin: 0 10px;
    }
  </style>
</head>
<body>

<div class="poster-page">
  <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="Poster" class="poster-image">
  <div class="poster-details">
    <h2>
      <?= htmlspecialchars($row['title_en']) ?>
      <?php if (!empty($row['title_he'])): ?>
        <br><?= htmlspecialchars($row['title_he']) ?>
      <?php endif; ?>
    </h2>

    <p><strong>🗓️ שנה:</strong> <?= htmlspecialchars($row['year']) ?></p>
    <p><strong>⭐ דירוג IMDb:</strong>
      <?= !empty($row['imdb_rating']) ? htmlspecialchars($row['imdb_rating']) . ' / 10' : 'לא זמין' ?>
    </p>
    <?php if (!empty($row['plot'])): ?>
      <p><strong>📘 תקציר:</strong><br><?= htmlspecialchars($row['plot']) ?></p>
    <?php endif; ?>
    <?php if (!empty($row['imdb_link'])): ?>
      <p><strong>🔗 IMDb:</strong>
        <a href="<?= htmlspecialchars($row['imdb_link']) ?>" target="_blank">מעבר לקישור</a>
      </p>
    <?php endif; ?>

    <!-- תגיות -->
    <div class="poster-tags">
      <strong>🏷️ תגיות:</strong><br>
      <?php
      $cat_result = $conn->query("SELECT c.name FROM categories c
        JOIN poster_categories pc ON c.id = pc.category_id
        WHERE pc.poster_id = $id");
      if ($cat_result->num_rows > 0) {
        while ($cat = $cat_result->fetch_assoc()) {
          echo "<span class='tag'>" . htmlspecialchars($cat['name']) . "</span> ";
        }
      } else {
        echo "<span style='color:#999;'>אין תגיות</span>";
      }
      ?>
    </div>

    <div class="actions">
      <a href="edit.php?id=<?= $row['id'] ?>">✏️ ערוך</a> |
      <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('למחוק את הפוסטר?')">🗑️ מחק</a> |
      <a href="index.php">⬅ חזרה</a>
    </div>
  </div>
</div>

</body>
</html>

<?php $conn->close(); ?>
