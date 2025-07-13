<?php
session_start();
include 'header.php';

$conn = new mysqli('localhost', 'root', '123456', 'media');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// הגדרות תצוגה ודפדוף
$allowed_limits = [5, 10, 20, 50, 100, 250];
if (isset($_GET['limit'])) $_SESSION['limit'] = (int)$_GET['limit'];
$limit = $_SESSION['limit'] ?? 20;
$limit = in_array($limit, $allowed_limits) ? $limit : 20;

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

if (isset($_GET['view'])) $_SESSION['view_mode'] = $_GET['view'];
$view = $_SESSION['view_mode'] ?? 'grid';

// קלטים מהטופס
$search       = $_GET['search'] ?? '';
$year         = $_GET['year'] ?? '';
$min_rating   = $_GET['min_rating'] ?? '';
$imdb_id      = $_GET['imdb_id'] ?? '';
$type         = $_GET['type'] ?? '';
$genre        = $_GET['genre'] ?? '';
$actor        = $_GET['actor'] ?? '';
$search_mode  = $_GET['search_mode'] ?? 'or';

// תנאים
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
buildCondition('imdb_id',  $imdb_id,$search_mode, $where, $params, $types);
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
if (!empty($_GET['is_dubbed']))            $where[] = "is_dubbed = 1";
if (!empty($_GET['is_netflix_exclusive'])) $where[] = "is_netflix_exclusive = 1";
if (!empty($_GET['is_foreign_language']))  $where[] = "is_foreign_language = 1";
if (!empty($_GET['missing_translation']))  $where[] = "has_translation = 0";

// מיון
$orderBy = "";
if (!empty($_GET['sort'])) {
  switch ($_GET['sort']) {
    case 'year_asc':    $orderBy = "ORDER BY year ASC"; break;
    case 'year_desc':   $orderBy = "ORDER BY year DESC"; break;
    case 'rating_desc': $orderBy = "ORDER BY CAST(SUBSTRING_INDEX(imdb_rating, '/', 1) AS DECIMAL(3,1)) DESC"; break;
  }
}

// שליפה בפועל
$sql = "SELECT * FROM posters";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " $orderBy LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($sql);
if (!$stmt) die("❌ SELECT Error: " . $conn->error);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$rows = [];
while ($row = $result->fetch_assoc()) $rows[] = $row;
$stmt->close();

// חישוב סך הכל לשימוש ב־home.php
$count_sql = "SELECT COUNT(*) AS c FROM posters";
if ($where) $count_sql .= " WHERE " . implode(" AND ", $where);
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) $count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_rows = $count_result->fetch_assoc()['c'] ?? 0;
$count_stmt->close();
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>🔍 טופס סינון פוסטרים</title>
  <style>
    body {
      font-family: Arial;
      background:#f0f0f0;
      text-align: center;
      direction: rtl;
      padding: 40px;
    }
    form input, form select, form button {
      padding: 6px;
      margin: 4px;
    }
  </style>
</head>
<body>

<h2>🔍 טופס סינון פוסטרים</h2>

<form method="get" action="home.php">
  <input type="text" name="search" placeholder="🎬 שם" value="<?= htmlspecialchars($search) ?>">
  <input type="text" name="year" placeholder="🗓 שנה" value="<?= htmlspecialchars($year) ?>">
  <input type="text" name="min_rating" placeholder="⭐ דירוג מינימלי" value="<?= htmlspecialchars($min_rating) ?>">
  <input type="text" name="imdb_id" placeholder="🔗 IMDb ID" value="<?= htmlspecialchars($imdb_id) ?>">
  <input type="text" name="genre" placeholder="🎭 ז'אנר" value="<?= htmlspecialchars($genre) ?>">
  <input type="text" name="actor" placeholder="👥 שחקנים" value="<?= htmlspecialchars($actor) ?>"><br>

  <select name="type">
    <option value="">סוג</option>
    <option value="movie" <?= $type == 'movie' ? 'selected' : '' ?>>🎬 סרט</option>
    <option value="series" <?= $type == 'series' ? 'selected' : '' ?>>📺 סדרה</option>
    <option value="miniseries" <?= $type == 'miniseries' ? 'selected' : '' ?>>📺 מיני־סדרה</option>
    <option value="short" <?= $type == 'short' ? 'selected' : '' ?>>🎞️ קצר</option>
  </select>

  <select name="sort">
    <option value="">מיון</option>
    <option value="year_asc" <?= ($_GET['sort'] ?? '') == 'year_asc' ? 'selected' : '' ?>>שנה ↑</option>
    <option value="year_desc" <?= ($_GET['sort'] ?? '') == 'year_desc' ? 'selected' : '' ?>>שנה ↓</option>
    <option value="rating_desc" <?= ($_GET['sort'] ?? '') == 'rating_desc' ? 'selected' : '' ?>>דירוג ↓</option>
  </select>

  <select name="view">
    <option value="">תצוגה</option>
    <option value="default" <?= $view === 'default' ? 'selected' : '' ?>>🔤 רגילה</option>
    <option value="grid" <?= $view === 'grid' ? 'selected' : '' ?>>🧱 Grid</option>
    <option value="list" <?= $view === 'list' ? 'selected' : '' ?>>📋 List</option>
  </select>

  <select name="limit">
    <?php foreach ($allowed_limits as $opt): ?>
      <option value="<?= $opt ?>" <?= $limit == $opt ? 'selected' : '' ?>><?= $opt ?></option>
    <?php endforeach; ?>
  </select>

  <p style="margin:10px 0;">
    🔧 מצב חיפוש בין תנאים:  
    <strong>OR</strong> — לפחות תנאי אחד חייב להתקיים |
    <strong>AND</strong> — כל התנאים חייבים להתקיים
  </p>

  <label><input type="radio" name="search_mode" value="or" <?= $search_mode == 'or' ? 'checked' : '' ?>> OR</label>
  <label><input type="radio" name="search_mode" value="and" <?= $search_mode == 'and' ? 'checked' : '' ?>> AND</label><br>

  <label><input type="checkbox" name="is_dubbed" value="1" <?= isset($_GET['is_dubbed']) ? 'checked' : '' ?>> מדובב</label>
  <label><input type="checkbox" name="is_netflix_exclusive" value="1" <?= isset($_GET['is_netflix_exclusive']) ? 'checked' : '' ?>> בלעדי לנטפליקס</label>
  <label><input type="checkbox" name="is_foreign_language" value="1" <?= isset($_GET['is_foreign_language']) ? 'checked' : '' ?>> שפה זרה</label>
  <label><input type="checkbox" name="missing_translation" value="1" <?= isset($_GET['missing_translation']) ? 'checked' : '' ?>> חסר תרגום</label><br><br>

  <button type="submit">📥 סנן</button>
  <a href="home.php">🔄 איפוס</a>
</form>

</body>
</html>
