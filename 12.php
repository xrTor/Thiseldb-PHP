<?php
session_start();
include 'header.php';

$conn = new mysqli('localhost', 'root', '123456', 'media');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$allowed_limits = [10, 20, 50, 100];
$limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], $allowed_limits)
  ? (int)$_GET['limit']
  : 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

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

if (!empty($langs)) {
  $lang_conditions = [];
  foreach ($langs as $lang) {
    $lang_conditions[] = "languages LIKE ?";
    $params[] = "%$lang%";
    $types .= 's';
  }
  $where[] = '(' . implode(' OR ', $lang_conditions) . ')';
}

if (!empty($_GET['is_dubbed']))              $where[] = "is_dubbed = 1";
if (!empty($_GET['is_netflix_exclusive']))   $where[] = "is_netflix_exclusive = 1";
if (!empty($_GET['is_foreign_language']))    $where[] = "is_foreign_language = 1";
if (!empty($_GET['missing_translation']))    $where[] = "has_translation = 0";

$orderBy = "";
switch ($_GET['sort'] ?? '') {
  case 'year_asc':    $orderBy = "ORDER BY year ASC"; break;
  case 'year_desc':   $orderBy = "ORDER BY year DESC"; break;
  case 'rating_desc': $orderBy = "ORDER BY CAST(SUBSTRING_INDEX(imdb_rating, '/', 1) AS DECIMAL(3,1)) DESC"; break;
}

$sql_count = "SELECT COUNT(*) AS total FROM posters";
if ($where) $sql_count .= " WHERE " . implode(" AND ", $where);

$stmt_count = $conn->prepare($sql_count);
$total_rows = 0;
$total_pages = 1;

if ($stmt_count) {
  if ($types) $stmt_count->bind_param($types, ...$params);
  $stmt_count->execute();
  $result_count = $stmt_count->get_result();
  if ($result_count) {
    $total_rows = $result_count->fetch_assoc()['total'];
    $total_pages = ($limit > 0) ? max(1, ceil($total_rows / $limit)) : 1;
  }
  $stmt_count->close();
}
?>

<form method="get">
  <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="שם הפוסטר">
  <input type="text" name="actor" value="<?= htmlspecialchars($actor) ?>" placeholder="שחקן">
  <input type="text" name="imdb_id" value="<?= htmlspecialchars($imdb_id) ?>" placeholder="קוד IMDb">
  <input type="text" name="type" value="<?= htmlspecialchars($type) ?>" placeholder="סוג תוכן">

  <select name="genre">
    <option value="">-- ז׳אנר --</option>
    <option value="Action" <?= $genre === 'Action' ? 'selected' : '' ?>>Action</option>
    <option value="Comedy" <?= $genre === 'Comedy' ? 'selected' : '' ?>>Comedy</option>
    <option value="Drama"  <?= $genre === 'Drama'  ? 'selected' : '' ?>>Drama</option>
  </select>

  <select name="year">
    <option value="">-- שנה --</option>
    <?php for ($y = date('Y'); $y >= 1980; $y--): ?>
      <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
    <?php endfor; ?>
  </select>

  <input type="number" step="0.1" name="min_rating" value="<?= htmlspecialchars($min_rating) ?>" placeholder="דירוג מינימלי">

  <select name="sort">
    <option value="">-- מיון --</option>
    <option value="year_asc"    <?= ($_GET['sort'] ?? '') === 'year_asc' ? 'selected' : '' ?>>שנה ↑</option>
    <option value="year_desc"   <?= ($_GET['sort'] ?? '') === 'year_desc' ? 'selected' : '' ?>>שנה ↓</option>
    <option value="rating_desc" <?= ($_GET['sort'] ?? '') === 'rating_desc' ? 'selected' : '' ?>>דירוג ↓</option>
  </select>

  <select name="limit">
    <?php foreach ($allowed_limits as $l): ?>
      <option value="<?= $l ?>" <?= $limit === $l ? 'selected' : '' ?>><?= $l ?> תוצאות</option>
    <?php endforeach; ?>
  </select>

  <div>
    <label><input type="radio" name="search_mode" value="or" <?= $search_mode === 'or' ? 'checked' : '' ?>> תנאי OR</label>
    <label><input type="radio" name="search_mode" value="and" <?= $search_mode === 'and' ? 'checked' : '' ?>> תנאי AND</label>
  </div>

  <label><input type="checkbox" name="is_dubbed" <?= !empty($_GET['is_dubbed']) ? 'checked' : '' ?>> מדובב</label>
  <label><input type="checkbox" name="is_netflix_exclusive" <?= !empty($_GET['is_netflix_exclusive']) ? 'checked' : '' ?>> בלעדי לנטפליקס</label>
  <label><input type="checkbox" name="is_foreign_language" <?= !empty($_GET['is_foreign_language']) ? 'checked' : '' ?>> שפה זרה</label>
  <label><input type="checkbox" name="missing_translation" <?= !empty($_GET['missing_translation']) ? 'checked' : '' ?>> חסר תרגום</label>

  <button type="submit">חפש</button>
</form>
