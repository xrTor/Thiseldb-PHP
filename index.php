<?php require_once 'server.php';?>
<?php include 'header.php'; ?>

<?php
// ⬇️ כאן בדיוק!
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;

$count_result = $conn->query("SELECT COUNT(*) AS total FROM posters");
$total_rows   = $count_result->fetch_assoc()['total'];
$total_pages  = max(1, ceil($total_rows / $limit)); // תמיד לפחות עמוד אחד

if ($page > $total_pages) {
  $page = $total_pages;
}

$offset = ($page - 1) * $limit;

// ⬇️ ואז שליפת הפוסטרים:
$result = $conn->query("SELECT * FROM posters ORDER BY id DESC LIMIT $limit OFFSET $offset");





$where = [];
$params = [];
$types = '';

if (!empty($_GET['tag'])) {
    $where[] = "id IN (
        SELECT poster_id FROM poster_categories WHERE category_id = ?
    )";
    $params[] = intval($_GET['tag']);
    $types .= 'i';
}

if (!empty($_GET['search'])) {
    $where[] = "(title_en LIKE ? OR title_he LIKE ?)";
    $search = "%" . $_GET['search'] . "%";
    $params[] = $search;
    $params[] = $search;
    $types .= 'ss';
}

if (!empty($_GET['year'])) {
    $where[] = "year LIKE ?";
    $params[] = "%" . $_GET['year'] . "%";
    $types .= 's';
}

if (!empty($_GET['min_rating'])) {
    $where[] = "CAST(SUBSTRING_INDEX(imdb_rating, '/', 1) AS DECIMAL(3,1)) >= ?";
    $params[] = floatval($_GET['min_rating']);
    $types .= 'd';
}

if (!empty($_GET['type'])) {
    $where[] = "type = ?";
    $params[] = $_GET['type'];
    $types .= 's';
}
$orderBy = "";
if (!empty($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'year_asc': $orderBy = "ORDER BY year ASC"; break;
        case 'year_desc': $orderBy = "ORDER BY year DESC"; break;
        case 'rating_desc':
            $orderBy = "ORDER BY CAST(SUBSTRING_INDEX(imdb_rating, '/', 1) AS DECIMAL(3,1)) DESC";
            break;
    }
}

if (empty($orderBy)) {
    $orderBy = "ORDER BY id DESC"; // ✅ מיון ברירת מחדל לפי חדש
}



$sql = "SELECT * FROM posters";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " $orderBy LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
if (!empty($_GET['subtitles'])) {
    $where[] = "has_subtitles = 1";
}

?>

<!DOCTYPE html>
<html lang="he">
<head>
  <meta charset="UTF-8">
  <title>Thiseldb</title>
  <link rel="stylesheet" href="style.css">
<link rel="script" href="script.js">
</head>
<body class="rtl">



  <?php
  $tags = $conn->query("SELECT * FROM categories");
  $current_tag = $_GET['tag'] ?? null;
  ?>


  <div class="poster-wall">
    <?php if (empty($rows)): ?>
      <p class="no-results">לא נמצאו תוצאות 😢</p>
    <?php else: ?>
     <?php foreach ($rows as $row): ?>
  <?php $poster_id = $row['id']; ?>
  <div class="poster ltr">
    <!-- תמונה -->
    <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="<?= htmlspecialchars($row['title_en']) ?>">

    
    <!-- כותרת -->
    <div class="poster-title ltr">
      <b>
        <?= htmlspecialchars($row['title_en']) ?>
        <?php if (!empty($row['title_he'])): ?><br><?= htmlspecialchars($row['title_he']) ?><?php endif; ?>
      </b><br>
      [<?= $row['year'] ?>]
    </div>

    <!-- דירוג ו־IMDb -->


<span class="imdb-first">
  <?php if ($row['imdb_link']): ?>
    <a href="<?= $row['imdb_link'] ?>" target="_blank" style="display:inline-flex; align-items:center; gap:1px;">
      <img src="IMDb.png" class="imdb ltr" alt="IMDb" style="height:18px;"> <span>⭐<?= htmlspecialchars($row['imdb_rating']) ?> / 10</span>
    </a>

<?php
// סמל לפי שפת מקור
$lang_icons = [
  'en' => '', 'he' => '🇮🇱', 'fr' => '🇫🇷',
  'es' => '🇪🇸', 'ja' => '🇯🇵', 'de' => '🇩🇪'
];
$lang = $row['lang_code'] ?? '';
$lang_icon = $lang_icons[$lang] ?? '';
/* 🌐 */

// תכונות נוספות
$features = '';
if (!empty($row['is_dubbed'])) {
  $features .= ' <span title="מדובב"><img src="hebdub.svg" class="bookmark1"></span>';
}
if (!empty($row['has_subtitles'])) {
  $features .= ' <span title="כתוביות">📝</span>';
}

echo "<div style='margin-top:6px; font-size:16px;'>$lang_icon $features</div>";
?>

      <?php endif; ?></span>

<?php
$type = $row['type'] ?? 'movie';

switch ($type) {
  case 'movie':
    $icon = '🎬 סרט';
    break;
  case 'series':
    $icon = '📺 סדרה';
    break;
  case 'short':
    $icon = '🎞️ סרט קצר';
    break;
  case 'miniseries':
    $icon = '📺 מיני-סדרה';
    break;
  default:
    $icon = '❓ לא ידוע';
}

echo "<div style='font-size:12px; color:#555;'>$icon</div>";
?>



<!--
      <img src="bookmark1.png" class="bookmark1" alt="">
      <img src="unlikew.ico" class="bookmark1" alt="">
      <img src="likew.ico" class="bookmark1" alt="">
    
-->


    <!-- ✏️🗑️ פעולות בתחתית -->
    <div class="poster-actions rtl" style="margin-top:10px; font-size:13px; text-align:center;">
      <a href="edit.php?id=<?= $poster_id ?>">✏️ עריכה</a> |
      <a href="delete.php?id=<?= $poster_id ?>" onclick="return confirm('למחוק את הפוסטר?')">🗑️ מחיקה</a>
    </div>



<div class="view-link rtl" style="margin-top:10px; text-align:center;">
  <a href="poster.php?id=<?= $row['id'] ?>">📄 צפה בפוסטר</a>
<?php if ($row['type'] === 'series' && !empty($row['tvdb_id'])): ?>
  <div style="text-align:center; margin-top:6px;">
    <a href="<?= htmlspecialchars($row['tvdb_id']) ?>" target="_blank">
       צפה בסדרה ב־TVDB
    </a>
  </div>

  
<?php endif; ?>

</div>  </div>


<?php endforeach; ?>



    <?php endif; ?>
  </div>

  
  <div style="text-align:center;">
  <?php if ($page > 1): ?>
    <a href="index.php?page=<?= $page - 1 ?>">⬅ הקודם</a>
  <?php endif; ?>
  <a href="index.php?page=<?= $page + 1 ?>">הבא ➡</a>
</div>
<p style="text-align:center;">עמוד <?= $page ?> מתוך <?= $total_pages ?></p>

</body>
</html>
<?php
/*
<pre>
<?php print_r($row); ?>
</pre>
*/
?>

<?php $conn->close(); ?>

<?php include 'footer.php'; ?>