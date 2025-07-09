<?php
include 'bar.php';
?>
<!DOCTYPE html>
<html lang="he">
<head>
  <meta charset="UTF-8">
  <title>ספריית מדיה</title>
  <link rel="stylesheet" href="style.css">
  <style>
/* עיצוב גוף הדף */
body {
  direction: rtl;
  font-family: Arial, sans-serif;
  background-color: #f0f0f0;
  margin: 0;
  padding: 0;
}

/* תצוגת Grid */
.poster {
  background: #fff;
  border: 1px solid #ccc;
  padding: 10px;
  text-align: center;
  transition: box-shadow 0.3s ease;
}

.poster:hover {
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.poster img {
  max-width: 100%;
  object-fit: cover;
}

/* תצוגת List */
.poster-list {
  list-style: none;
  padding: 20px;
}

.poster-list li {
  padding: 8px;
  border-bottom: 1px solid #ccc;
  display: flex;
  align-items: center;
  gap: 10px;
}

.poster-list li img {
  height: 60px;
}

/* מצב רגיל — בדיוק כמו בהתחלה */
.poster-regular {
  list-style: none;
  padding: 20px;
  margin: 0;
  text-align: center;
}

.poster-regular li {
  display: inline-block;
  margin: 10px;
  vertical-align: top;
  width: 180px;
}

.poster-regular li img {
  height: 150px;
  border-radius: 4px;
  display: block;
  margin: 0 auto 6px;
}

.poster-regular li strong {
  display: block;
  font-size: 14px;
  margin-bottom: 4px;
}

.poster-regular li a {
  text-decoration: none;
  color: #333;
}

.poster-regular li a:hover {
  color: #0078d4;
}

  </style>
</head>
<body>

<!-- 🖼️ תצוגת פוסטרים -->
<?php if (empty($rows)): ?>
  <p style="text-align:center;">😢 לא נמצאו תוצאות</p>

<?php elseif ($view === 'grid'): ?>
  <!-- תצוגת Grid -->
  <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:20px; padding:20px;">
    <?php foreach ($rows as $row): ?>
      <div class="poster">
        <a href="poster.php?id=<?= $row['id'] ?>">
          <img src="<?= htmlspecialchars($row['image_url']) ?>">
          <strong><?= htmlspecialchars($row['title_en']) ?></strong>
          <?= htmlspecialchars($row['title_he']) ?><br>
          [<?= $row['year'] ?>]
        </a>
        <?php if ($row['imdb_link']): ?>
          <div><a href="<?= $row['imdb_link'] ?>" target="_blank">⭐ <?= $row['imdb_rating'] ?>/10 IMDb</a></div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

<?php elseif ($view === 'list'): ?>
  <!-- תצוגת List -->
  <ul class="poster-list">
    <?php foreach ($rows as $row): ?>
      <li>
        <img src="<?= htmlspecialchars($row['image_url']) ?>">
        <strong><?= htmlspecialchars($row['title_en']) ?></strong> —
        <?= htmlspecialchars($row['title_he']) ?> (<?= $row['year'] ?>)
        ⭐ <?= $row['imdb_rating'] ?>
        <a href="poster.php?id=<?= $row['id'] ?>">📄 צפייה</a>
      </li>
    <?php endforeach; ?>
  </ul>

<?php else: ?>
  <!-- תצוגה רגילה -->
  <ul class="poster-regular">
    <?php foreach ($rows as $row): ?>
      <li>
        <a href="poster.php?id=<?= $row['id'] ?>">
          <img src="<?= htmlspecialchars($row['image_url']) ?>">
          <strong><?= htmlspecialchars($row['title_en']) ?></strong>
          <?= htmlspecialchars($row['title_he']) ?><br>
          [<?= $row['year'] ?>]
        </a>
        <?php if ($row['imdb_link']): ?>
          <div><a href="<?= $row['imdb_link'] ?>" target="_blank">⭐ <?= $row['imdb_rating'] ?>/10 IMDb</a></div>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>


<!-- 📚 דפדוף -->
<div style="text-align:center; margin:20px;">
  <?php if ($page > 1): ?>
    <a href="home.php?page=<?= $page - 1 ?>">⬅ הקודם</a> |
  <?php endif; ?>
  עמוד <?= $page ?> מתוך <?= $total_pages ?>
  <?php if ($page < $total_pages): ?>
    | <a href="home.php?page=<?= $page + 1 ?>">הבא ➡</a>
  <?php endif; ?>
</div>

<p style="text-align:center;">תצוגה נוכחית: <?= htmlspecialchars($view) ?></p>
<?php include 'footer.php'; ?>
</body>
</html>
