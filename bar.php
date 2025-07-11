<?php
session_start();
include 'header.php';

$conn = new mysqli('localhost', 'root', '123456', 'media');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// הגדרות תצוגה
$allowed_limits = [5, 10, 20, 50, 100, 250];
$limit = in_array($_GET['limit'] ?? 20, $allowed_limits) ? (int)$_GET['limit'] : 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;
$view = $_GET['view'] ?? 'grid';

// קלטים
$search       = $_GET['search'] ?? '';
$year         = $_GET['year'] ?? '';
$min_rating   = $_GET['min_rating'] ?? '';
$imdb_id      = $_GET['imdb_id'] ?? '';
$type         = $_GET['type'] ?? '';
$genre        = $_GET['genre'] ?? '';
$actor        = $_GET['actor'] ?? '';
$search_mode  = $_GET['search_mode'] ?? 'or';
$langs        = $_GET['languages'] ?? [];

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

if ($min_rating !== '') {
  $where[] = "CAST(SUBSTRING_INDEX(imdb_rating, '/', 1) AS DECIMAL(3,1)) >= ?";
  $params[] = (float)$min_rating;
  $types .= 'd';
}

if ($type !== '') {
  $where[] = "type = ?";
  $params[] = $type;
  $types .= 's';
}

// שפות כתוביות
if (!empty($langs)) {
  $lang_conditions = [];
  foreach ($langs as $lang) {
    $lang_conditions[] = "languages LIKE ?";
    $params[] = "%$lang%";
    $types .= 's';
  }
  $where[] = '(' . implode(' OR ', $lang_conditions) . ')';
}

// תיבות סימון
if (!empty($_GET['is_dubbed']))              $where[] = "is_dubbed = 1";
if (!empty($_GET['is_netflix_exclusive']))   $where[] = "is_netflix_exclusive = 1";
if (!empty($_GET['is_foreign_language']))    $where[] = "is_foreign_language = 1";
if (!empty($_GET['missing_translation']))    $where[] = "has_translation = 0";

// מיון
$orderBy = "";
switch ($_GET['sort'] ?? '') {
  case 'year_asc':    $orderBy = "ORDER BY year ASC"; break;
  case 'year_desc':   $orderBy = "ORDER BY year DESC"; break;
  case 'rating_desc': $orderBy = "ORDER BY CAST(SUBSTRING_INDEX(imdb_rating, '/', 1) AS DECIMAL(3,1)) DESC"; break;
}

// ספירה עם טיפול בשגיאה
$sql_count = "SELECT COUNT(*) AS total FROM posters";
if ($where) $sql_count .= " WHERE " . implode(" AND ", $where);

$stmt_count = $conn->prepare($sql_count);
if (!$stmt_count) {
  echo "<p style='color:red;'>❌ שגיאה ב־prepare (COUNT): " . htmlspecialchars($conn->error) . "</p>";
  $total_rows = 0;
  $total_pages = 1;
} else {
  if ($types) $stmt_count->bind_param($types, ...$params);
  $stmt_count->execute();
  $result_count = $stmt_count->get_result();
  if ($result_count) {
    $total_rows = $result_count->fetch_assoc()['total'];
    $total_pages = max(1, ceil($total_rows / $limit));
  } else {
    $total_rows = 0;
    $total_pages = 1;
  }
  $stmt_count->close();
}

// שליפה עם טיפול בשגיאה
$sql = "SELECT * FROM posters";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " $orderBy LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($sql);
if (!$stmt) {
  echo "<p style='color:red;'>❌ שגיאה ב־prepare (SELECT): " . htmlspecialchars($conn->error) . "</p>";
  $rows = [];
} else {
  if ($types) $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $result = $stmt->get_result();
  $rows = [];
  while ($row = $result->fetch_assoc()) $rows[] = $row;
  $stmt->close();
}
?>
