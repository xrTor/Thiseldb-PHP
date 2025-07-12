<?php
session_start();
include 'header.php';

$conn = new mysqli('localhost', 'root', '123456', 'media');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// תצוגה
$allowed_limits = [5, 10, 20, 50, 100, 250];
$limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], $allowed_limits) ? (int)$_GET['limit'] : 20;
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// נתונים
$total_rows = (int)$conn->query("SELECT COUNT(*) AS c FROM posters")->fetch_assoc()['c'];
$total_pages = ($limit > 0) ? max(1, ceil($total_rows / $limit)) : 1;
$result = $conn->query("SELECT * FROM posters ORDER BY id DESC LIMIT $limit OFFSET $offset");
?>

<h2>🎬 פוסטרים אחרונים</h2>

<?php if ($result && $result->num_rows > 0): ?>
  <div style="display:flex; flex-wrap:wrap; gap:20px;">
    <?php while ($row = $result->fetch_assoc()): ?>
      <div style="width:180px; text-align:center;">
        <a href="poster.php?id=<?= $row['id'] ?>">
          <img src="<?= htmlspecialchars($row['image_url']) ?>" style="width:100%; border-radius:6px;">
          <div><?= htmlspecialchars($row['title_en']) ?></div>
        </a>
      </div>
    <?php endwhile; ?>
  </div>
<?php else: ?>
  <p style="background:#ffe; border:1px solid #cc9; padding:12px; border-radius:6px;">😢 לא נמצאו פוסטרים להצגה</p>
<?php endif; ?>

<p style="margin-top:30px;">
  עמוד <?= $page ?> מתוך <?= $total_pages ?>
  <?php if ($page > 1): ?> | <a href="index.php?page=<?= $page - 1 ?>&limit=<?= $limit ?>">⬅ קודם</a> <?php endif; ?>
  <?php if ($page < $total_pages): ?> | <a href="index.php?page=<?= $page + 1 ?>&limit=<?= $limit ?>">➡ הבא</a> <?php endif; ?>
</p>

<?php
$conn->close();
include 'footer.php';
?>
