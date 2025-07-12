<?php
 include 'header.php'; 
session_start();
$conn = new mysqli('localhost', 'root', '123456', 'media');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// הגדרות תצוגה
$allowed_limits = [5, 10, 20, 50, 100, 250];
if (isset($_GET['limit'])) $_SESSION['limit'] = (int)$_GET['limit'];
$limit = $_SESSION['limit'] ?? 20;
$limit = in_array($limit, $allowed_limits) ? $limit : 20;

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

if (isset($_GET['view'])) $_SESSION['view_mode'] = $_GET['view'];
$view = $_SESSION['view_mode'] ?? 'grid';

$search       = $_GET['search'] ?? '';
$year         = $_GET['year'] ?? '';
$min_rating   = $_GET['min_rating'] ?? '';
$imdb_id      = $_GET['imdb_id'] ?? '';
$type         = $_GET['type'] ?? '';
$genre        = $_GET['genre'] ?? '';
$actor        = $_GET['actor'] ?? '';
$search_mode  = $_GET['search_mode'] ?? 'or';

$where = []; $params = []; $types = '';

function buildCondition($field, $input, $mode, &$where, &$params, &$types) {
  $values = array_filter(array_map('trim', explode(',', $input)));
  if ($values) {
    $glue = ($mode === 'and') ? ' AND ' : ' OR ';
    $parts = [];
    foreach ($values as $val) {
      $parts[] = "$field LIKE ?";
      $params[] = "%$val%";
      $types .= 's';
    }
    $where[] = '(' . implode($glue, $parts) . ')';
  }
}

buildCondition('title_en', $search, $search_mode, $where, $params, $types);
buildCondition('year',     $year,   $search_mode, $where, $params, $types);
buildCondition('imdb_link',$imdb_id,$search_mode, $where, $params, $types);
buildCondition('genre',    $genre,  $search_mode, $where, $params, $types);
buildCondition('actors',   $actor,  $search_mode, $where, $params, $types);

if (!empty($min_rating)) {
  $where[] = "CAST(SUBSTRING_INDEX(imdb_rating, '/', 1) AS DECIMAL(3,1)) >= ?";
  $params[] = (float)$min_rating;
  $types .= 'd';
}

if (!empty($type)) {
  $where[] = "type = ?";
  $params[] = $type;
  $types .= 's';
}

if (!empty($_GET['is_dubbed']))              $where[] = "is_dubbed = 1";
if (!empty($_GET['is_netflix_exclusive']))   $where[] = "is_netflix_exclusive = 1";
if (!empty($_GET['is_foreign_language']))    $where[] = "is_foreign_language = 1";
if (!empty($_GET['missing_translation']))    $where[] = "has_translation = 0";
// מיון
$orderBy = "";
if (!empty($_GET['sort'])) {
  switch ($_GET['sort']) {
    case 'year_asc':    $orderBy = "ORDER BY year ASC"; break;
    case 'year_desc':   $orderBy = "ORDER BY year DESC"; break;
    case 'rating_desc': $orderBy = "ORDER BY CAST(SUBSTRING_INDEX(imdb_rating, '/', 1) AS DECIMAL(3,1)) DESC"; break;
  }
}

// שאילתת COUNT
$sql_count = "SELECT COUNT(*) AS total FROM posters";
if ($where) $sql_count .= " WHERE " . implode(" AND ", $where);
$stmt_count = $conn->prepare($sql_count);
if (!$stmt_count) {
  die("❌ שגיאה בשאילתת COUNT: " . $conn->error . "<br><strong>SQL:</strong> " . htmlspecialchars($sql_count));
}
if ($types) $stmt_count->bind_param($types, ...$params);
$stmt_count->execute();
$total_rows = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = max(1, ceil($total_rows / $limit));
$stmt_count->close();

// שאילתת SELECT
$sql = "SELECT * FROM posters";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " $orderBy LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($sql);
if (!$stmt) {
  die("❌ שגיאה בשאילתת SELECT: " . $conn->error . "<br><strong>SQL:</strong> " . htmlspecialchars($sql));
}
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$rows = [];
while ($row = $result->fetch_assoc()) $rows[] = $row;
$stmt->close();
?>
<!DOCTYPE html>
<html lang="he">
<head>
  <meta charset="UTF-8">
  <title>ספריית מדיה</title>
  <link rel="stylesheet" href="style.css">
  <style>
body {
  direction: rtl;
  font-family: Arial, sans-serif;
  background-color: #f0f0f0;
  margin: 0;
  padding: 0;
}
.poster { background: #fff; border: 1px solid #ccc; padding: 10px; text-align: center; transition: box-shadow 0.3s ease; }
.poster:hover { box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
.poster img { max-width: 100%; object-fit: cover; }
.poster-list { list-style: none; padding: 20px; }
.poster-list li { padding: 8px; border-bottom: 1px solid #ccc; display: flex; align-items: center; gap: 10px; }
.poster-list li img { height: 60px; }
.poster-regular { list-style: none; padding: 20px; margin: 0; text-align: center; }
.poster-regular li { display: inline-block; margin: 10px; vertical-align: top; width: 180px; }
.poster-regular li img { height: 150px; border-radius: 4px; display: block; margin: 0 auto 6px; }
.poster-regular li strong { display: block; font-size: 14px; margin-bottom: 4px; }
.poster-regular li a { text-decoration: none; color: #333; }
.poster-regular li a:hover { color: #0078d4; }
  </style>
</head>
<body>

<form method="get" action="home.php" style="margin:20px; text-align:right;">
  <input type="text" name="search" placeholder="🎬 שם " value="<?= htmlspecialchars($search) ?>">
  <input type="text" name="year" placeholder="🗓 שנה" value="<?= htmlspecialchars($year) ?>">
  <input type="text" name="min_rating" placeholder="⭐ דירוג מינימלי" value="<?= htmlspecialchars($min_rating) ?>">
  <input type="text" name="imdb_id" placeholder="🔗 IMDb ID" value="<?= htmlspecialchars($imdb_id) ?>">
  <input type="text" name="genre" placeholder="🎭 ז'אנר" value="<?= htmlspecialchars($genre) ?>">
  <input type="text" name="actor" placeholder="👥 שחקנים" value="<?= htmlspecialchars($actor) ?>">
<br>
  <select name="type">
    <option value="">סוג</option>
    <option value="movie" <?= $type == 'movie' ? 'selected' : '' ?>>🎬 סרט</option>
    <option value="series" <?= $type == 'series' ? 'selected' : '' ?>>📺 סדרה</option>
  </select>

  <select name="sort">
    <option value="">מיון</option>
    <option value="year_asc" <?= ($_GET['sort'] ?? '') == 'year_asc' ? 'selected' : '' ?>>שנה ↑</option>
    <option value="year_desc" <?= ($_GET['sort'] ?? '') == 'year_desc' ? 'selected' : '' ?>>שנה ↓</option>
    <option value="rating_desc" <?= ($_GET['sort'] ?? '') == 'rating_desc' ? 'selected' : '' ?>>דירוג ↓</option>
  </select>

<select name="view" id="view">
  <option value="">תצוגה</option>
  <option value="default" <?= ($view === 'default' ? 'selected' : '') ?>>🔤 רגילה</option>
  <option value="grid" <?= ($view === 'grid' ? 'selected' : '') ?>>🧱 Grid</option>
  <option value="list" <?= ($view === 'list' ? 'selected' : '') ?>>📋 List</option>
</select>

<label for="limit">🔢 תוצאות לעמוד:</label>
<select name="limit" id="limit">
  <?php foreach ($allowed_limits as $opt): $selected = ($limit == $opt) ? 'selected' : ''; ?>
    <option value="<?= $opt ?>" <?= $selected ?>><?= $opt ?></option>
  <?php endforeach; ?>
</select>

<label><strong>🔧 מצב חיפוש בין תנאים:</strong></label>
<label><input type="radio" name="search_mode" value="or" <?= $search_mode == 'or' ? 'checked' : '' ?>> OR</label> |
<label><input type="radio" name="search_mode" value="and" <?= $search_mode == 'and' ? 'checked' : '' ?>> AND</label><br><br>

<label><input type="checkbox" name="is_dubbed" value="1" <?= isset($_GET['is_dubbed']) ? 'checked' : '' ?>> מדובב</label>
<label><input type="checkbox" name="is_netflix_exclusive" value="1" <?= isset($_GET['is_netflix_exclusive']) ? 'checked' : '' ?>> נטפליקס</label>
<label><input type="checkbox" id="is_foreign_language" name="is_foreign_language" value="1" <?= isset($_GET['is_foreign_language']) ? 'checked' : '' ?>> שפה זרה</label>
<label><input type="checkbox" name="missing_translation" value="1" <?= isset($_GET['missing_translation']) ? 'checked' : '' ?>> חסר תרגום</label>

<div id="languageMenu"><?php include 'flags.php'; ?></div>

<button type="submit">📥 סנן</button> <a href="home.php">🔄 איפוס</a>
</form>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const checkbox = document.getElementById('is_foreign_language');
  const menu = document.getElementById('languageMenu');
  if (checkbox && menu) {
    menu.style.display = checkbox.checked ? 'block' : 'none';
    checkbox.addEventListener('change', () => {
      menu.style.display = checkbox.checked ? 'block' : 'none';
    });
  }
});
</script>
<?php if (empty($rows)): ?>
  <p style="text-align:center;">😢 לא נמצאו תוצאות</p>

<?php elseif ($view === 'grid'): ?>
  <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:20px; padding:20px;">
    <?php foreach ($rows as $row): ?>
      <div class="poster">
        <a href="poster.php?id=<?= $row['id'] ?>">
          <img src="<?= htmlspecialchars($row['image_url']) ?>">
          <strong><?= htmlspecialchars($row['title_en']) ?></strong>
          <?= htmlspecialchars($row['title_he']) ?><br>[<?= $row['year'] ?>]
        </a>
        <?php if ($row['imdb_link']): ?>
          <div><a href="<?= $row['imdb_link'] ?>" target="_blank">⭐ <?= $row['imdb_rating'] ?>/10 IMDb</a></div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

<?php elseif ($view === 'list'): ?>
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
  <ul class="poster-regular">
    <?php foreach ($rows as $row): ?>
      <li>
        <a href="poster.php?id=<?= $row['id'] ?>">
          <img src="<?= htmlspecialchars($row['image_url']) ?>">
          <strong><?= htmlspecialchars($row['title_en']) ?></strong>
          <?= htmlspecialchars($row['title_he']) ?><br>[<?= $row['year'] ?>]
        </a>
        <?php if ($row['imdb_link']): ?>
          <div><a href="<?= $row['imdb_link'] ?>" target="_blank">⭐ <?= $row['imdb_rating'] ?>/10 IMDb</a></div>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<!-- 📚 דפדוף בין עמודים -->
<div style="text-align:center; margin:20px;">
  <?php if ($page > 1): ?>
    <a href="home.php?page=<?= $page - 1 ?>">⬅ הקודם</a> |
  <?php endif; ?>
  עמוד <?= $page ?> מתוך <?= $total_pages ?>
  <?php if ($page < $total_pages): ?>
    | <a href="home.php?page=<?= $page + 1 ?>">הבא ➡</a>
  <?php endif; ?>
</div>

<!-- 🧭 תצוגת מצב נוכחית -->
<p style="text-align:center;">תצוגה נוכחית: <?= htmlspecialchars($view) ?></p>

<!-- 🧱 סיום הדף -->
<?php include 'footer.php'; ?>
</body>
</html>
