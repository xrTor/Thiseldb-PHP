<?php
require_once 'server.php';
include 'header.php';

set_time_limit(3000000);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 250;
$offset = ($page - 1) * $per_page;

if ($id === 0) {
  echo "<p>❌ אוסף לא צוין</p>";
  include 'footer.php'; exit;
}

$stmt = $conn->prepare("SELECT * FROM collections WHERE id = ?");
$stmt->bind_param("i", $id); $stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
  echo "<p>❌ האוסף לא נמצא</p>";
  include 'footer.php'; exit;
}
$collection = $result->fetch_assoc();
$stmt->close();

// חיפוש (כל השדות)
$keyword = trim($_GET['q'] ?? '');
$where = "pc.collection_id = ?";
$params = [$id];
$types = "i";

if ($keyword) {
    $searchFields = [
        "p.title_en", "p.title_he", "p.plot", "p.plot_he", "p.actors", "p.genre",
        "p.directors", "p.writers", "p.producers", "p.composers", "p.cinematographers",
        "p.languages", "p.countries", "p.imdb_id", "p.year", "p.tvdb_id"
    ];
    $like = "%$keyword%";
    $where .= " AND (";
    $whereLike = [];
    foreach ($searchFields as $f) $whereLike[] = "$f LIKE ?";
    $where .= implode(" OR ", $whereLike) . ")";
    foreach ($searchFields as $f) {
        $params[] = $like;
        $types .= "s";
    }
}

$count_stmt = $conn->prepare("
    SELECT COUNT(*) FROM poster_collections pc
    JOIN posters p ON p.id = pc.poster_id
    WHERE $where
");
$count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_posters = $count_result->fetch_row()[0] ?? 0;
$count_stmt->close();
$total_pages = ceil($total_posters / $per_page);

// פוסטרים לדף הנוכחי
$sql = "
    SELECT p.* FROM poster_collections pc
    JOIN posters p ON p.id = pc.poster_id
    WHERE $where
    LIMIT $per_page OFFSET $offset
";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$poster_list = [];
while ($row = $res->fetch_assoc()) $poster_list[] = $row;

// כל הפוסטרים לאוסף (ברשימה השמית), ממויינים לפי סדר הוספה
$all_posters_for_list = [];
$sql_all = "
    SELECT p.* FROM poster_collections pc
    JOIN posters p ON p.id = pc.poster_id
    WHERE pc.collection_id = ?
    ORDER BY pc.poster_id ASC
";
$stmt_all = $conn->prepare($sql_all);
$stmt_all->bind_param("i", $id);
$stmt_all->execute();
$res_all = $stmt_all->get_result();
while ($row = $res_all->fetch_assoc()) $all_posters_for_list[] = $row;
$stmt_all->close();
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>📦 אוסף: <?= htmlspecialchars($collection['name']) ?></title>
  <style>
    body { font-family:Arial; background:#f9f9f9; padding:10px; direction:rtl; }
    .container { max-width:1100px; margin:auto; background:white; padding:20px; border-radius:6px; box-shadow:0 0 6px rgba(0,0,0,0.1); }
    .header-img { max-width:100%; border-radius:1px; margin-top:10px; }
    .description { margin-top:10px; color:#444; }
    .link-btn { background:#eee; padding:6px 12px; border-radius:6px; text-decoration:none; margin:10px 10px 0 0; display:inline-block; }
    .link-btn:hover { background:#ddd; }
    .poster-section { display:flex; flex-direction:row-reverse; gap:14px; margin-top:20px; align-items:flex-start; flex-wrap:nowrap !important; }
    .poster-grid { flex:1 1 0; min-width:0; display:flex; flex-wrap:wrap; }
    .poster-grid.small { gap:2px; }
    .poster-grid.medium { gap:8px; }
    .poster-grid.large { gap:12px; }
    .poster-item { text-align:center; position:relative; }
    .poster-item.small { width:100px; margin-bottom:0 !important; }
    .poster-item.medium { width:160px; }
    .poster-item.large { width:220px; }
    .poster-item img { width:100%; aspect-ratio:2/3; object-fit:cover; border-radius:1px; margin:0; box-shadow:0 0 4px rgba(0,0,0,0.1); }
    .poster-item.small small,
    .poster-item.small .title-he,
    .poster-item.small .imdb-id { font-size:10px; line-height:1; margin-top:1px !important; margin-bottom:0 !important; }
    .title-he { font-size:12px; color:#555; }
    .imdb-id a {  color: #99999A !important; font-size: 12px; text-decoration: none; }
    .remove-btn { background:#dc3545; color:white; border:none; padding:4px 8px; border-radius:4px; font-size:12px; cursor:pointer; margin-top:6px; }
    .delete-box { display:none; }
    .poster-list-sidebar {
      flex:none !important; width:305px; min-width:250px; max-width:320px; align-self:flex-start; box-sizing:border-box; overflow-y:auto;
      background:#f8f8f8; border-radius:6px; box-shadow:0 0 4px rgba(0,0,0,0.05); font-size:14px; color:#333; height:fit-content;
      text-align:right; padding:12px 8px; transition:all .3s;
      display:block;
    }
    .poster-list-sidebar.hide-list { display:none !important; }
    .poster-list-sidebar h4 { margin-top:0; font-size:16px; }
    .poster-list-sidebar input {
      width:100%; padding:8px 14px; border:1px solid #bbb; border-radius:8px;
      margin-bottom:10px; background:#fafcff;
      font-size:15px; box-shadow:0 1px 6px #0001;
      outline:none; transition:.17s;
      direction: rtl;
    }
    .poster-list-sidebar input:focus { border-color:#43a4e8; box-shadow:0 2px 10px #97e1ff45; }
    .poster-list-sidebar input:hover { border-color:#45bcd4; }
    .poster-list-sidebar input[type="text"] { cursor:pointer; }
    .poster-list-sidebar input[type="text"]:hover { background: #f3fbff; }
    .poster-list-sidebar input[type="text"]:focus { cursor:text; background: #fff; }
    .poster-list-sidebar input::placeholder { color: #aaa; font-size:15px;}
    .poster-list-sidebar ol { list-style-type:decimal !important; direction: rtl; padding-right: 20px; margin: 0; list-style-position: inside !important; }
    .poster-list-sidebar li { margin-bottom: 6px; line-height: 1.3; }
    .poster-list-sidebar .title-he, .poster-list-sidebar .imdb-id { font-size: 11px; line-height: 1.2; margin-top: 2px; margin-bottom: 0; }
    .poster-list-sidebar .year { font-size:10px; color:#555; }
    #poster-list { padding-right: 0; margin: 0; }

    .name-list-toggle-btn {
      display:inline-block;
      margin-right:8px; margin-bottom:5px;
      padding:8px 18px 8px 36px;
      background:#ededed; border-radius:13px;
      border:none; cursor:pointer;
      font-size:19px; color:#22644d;
      transition:background .15s, box-shadow .2s;
      font-family:inherit;
      position:relative;
      box-shadow:0 2px 8px #0001;
      vertical-align:middle;
    }
    .name-list-toggle-btn:hover { background:#e0f2f1; color:#125a50; box-shadow:0 2px 16px #b4fff965; }
    .name-list-toggle-btn .icon { font-size:21px; vertical-align:middle; position:absolute; left:10px; top:10px;}
    /* כפתורי הגדלים */
    .size-btn {
      padding:7px 19px;
      font-size:18px;
      margin:0 2px;
      background:#f6f6f8;
      color:#222;
      border-radius:8px;
      border:1.5px solid #b0b0b5;
      transition:.15s;
      cursor:pointer;
      font-family:inherit;
    }
    .size-btn.active,
    .size-btn:focus {
      background:#2c88ee !important; color:#fff !important; border-color:#125a50;
      outline: none;
    }
    .size-btn:hover { background:#e8f2ff; color:#125a50; }
    /* שדה הוספת מזהים */
    .form-box textarea {
      width: 100%;
      font-size: 15px;
      padding: 8px;
      border-radius: 7px;
      border: 1px solid #bbb;
      background: #fafcff;
      margin-bottom: 10px;
      resize: vertical;
      min-height: 65px;
    }
    .form-box button {
      width: 100%;
      font-size: 16px;
      padding: 8px 0;
      border-radius: 7px;
      border: none;
      background: #007bff;
      color: #fff;
      margin-top: 6px;
      cursor: pointer;
      transition: background 0.2s;
    }
    .form-box button:hover {
      background: #0056b3;
    }
    /* שורת החיפוש הראשית */
    .main-search-box {
      background: #fff;
      border: 1.5px solid #bbb;
      border-radius: 11px;
      padding: 10px 18px;
      width: 270px;
      font-size: 16px;
      box-shadow: 0 2px 10px #e1e6eb50;
      outline: none;
      transition: border .2s, box-shadow .2s;
      margin-left: 0;
      margin-right: 0;
      color: #1d1d1d;
    }
    .main-search-box:focus {
      border: 1.5px solid #1a91e6;
      box-shadow: 0 2px 13px #b0e2ff90;
    }
    .main-search-box::placeholder {
      color: #aaa;
      font-size: 15px;
    }
    .main-search-box:hover {
      border-color: #5dc2ff;
    }
    .main-search-btn {
      padding: 8px 20px;
      border-radius: 7px;
      border: none;
      background: #1576cc;
      color: #fff;
      font-size: 16px;
      cursor: pointer;
      transition: background .17s, color .17s;
    }
    .main-search-btn:hover {
      background: #10579b;
      color: #fff;
    }
    /* רספונסיביות קלה */
    @media (max-width:1000px) {
      .container { padding: 8px 2px; }
      .poster-section { flex-direction:column; gap:0; }
      .poster-list-sidebar { width:100%; max-width:100%; margin-bottom:16px; }
    }
  </style>
</head>
<body><br>
<div class="container">
  <h2>📦 אוסף: <?= htmlspecialchars($collection['name']) ?></h2>
  <?php if (!empty($collection['image_url'])): ?>
    <img src="<?= htmlspecialchars($collection['image_url']) ?>" alt="תמונה" class="header-img">
  <?php endif; ?>
  <?php if (!empty($collection['description'])): ?>
    <div class="description">📝 <?= nl2br(htmlspecialchars($collection['description'])) ?></div>
  <?php endif; ?>

  <div><button type="button" class="name-list-toggle-btn" onclick="toggleNameList()">
      <span class="icon">📄</span> הצג/הסתר רשימה שמית
    </button>
    <a href="edit_collection.php?id=<?= $collection['id'] ?>" class="link-btn">✏️ ערוך</a>
    <a href="manage_collections.php?delete=<?= $collection['id'] ?>" onclick="return confirm('למחוק את האוסף?')" class="link-btn" style="background:#fdd;">🗑️ מחק</a>
    <a href="collections.php" class="link-btn">⬅ חזרה לרשימת האוספים</a>
    <a href="#" onclick="toggleDelete()" class="link-btn" style="background:#ffc;">🧹 הצג/הסתר מחיקה</a>
  </div>

  <div style="margin-top:10px;">
    
    גודל פוסטרים:
    <button onclick="setSize('small')" class="size-btn" id="size-small">קטן</button>
    <button onclick="setSize('medium')" class="size-btn" id="size-medium">בינוני</button>
    <button onclick="setSize('large')" class="size-btn" id="size-large">גדול</button>
    <a href="collection_csv.php?id=<?= $id ?>" target="_blank" class="size-btn" style="margin-right:14px; padding:8px 20px; border-radius:7px; border:none; background:#2a964a; color:#fff; text-decoration:none !important;">⬇️ ייצא רשימה כ־CSV</a>
  </div>

  <!-- חיפוש פוסטרים בתוך האוסף -->
  <form method="get" style="margin:12px 0 15px 0; display:flex; gap:10px; align-items:center; justify-content:center;">
    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="text" name="q" value="<?= htmlspecialchars($keyword) ?>" class="main-search-box" placeholder="🔎 חפש שם, שנה, במאי, שפה, מזהה IMDb ועוד...">
    <button type="submit" class="main-search-btn">חפש</button>
    <?php if ($keyword): ?>
      <a href="collection.php?id=<?= $id ?>" style="color:#1576cc; margin-right:7px;">נקה חיפוש</a>
    <?php endif; ?>
  </form>
  <!-- סוף חיפוש -->

  <h3>🎬 פוסטרים באוסף:</h3>
  <?php if ($poster_list): ?>
    <div class="poster-section">
      <div class="poster-grid medium">
        <?php foreach ($poster_list as $p): ?>
          <div class="poster-item medium">
            <a href="poster.php?id=<?= $p['id'] ?>">
              <?php $img = trim($p['image_url'] ?? '') ?: 'images/no-poster.png'; ?>
              <img src="<?= htmlspecialchars($img) ?>" alt="Poster">
              <small><?= htmlspecialchars($p['title_en']) ?></small>
              <?php if (!empty($p['title_he'])): ?>
                <div class="title-he"><?= htmlspecialchars($p['title_he']) ?></div>
              <?php endif; ?>
              <?php if (!empty($p['imdb_id'])): ?>
                <div class="imdb-id">
                  <a href="https://www.imdb.com/title/<?= htmlspecialchars($p['imdb_id']) ?>" target="_blank">
                    IMDb: <?= htmlspecialchars($p['imdb_id']) ?>
                  </a>
                </div>
              <?php endif; ?>
            </a>
            <div class="delete-box">
              <form method="post" action="remove_from_collection.php">
                <input type="hidden" name="collection_id" value="<?= $collection['id'] ?>">
                <input type="hidden" name="poster_id" value="<?= $p['id'] ?>">
                <button type="submit" class="remove-btn">🗑️ הסר</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="poster-list-sidebar" id="name-list-sidebar">
        <h4>📃 רשימה שמית</h4>
        <div style="position:relative;">
          <input type="text" id="poster-search" placeholder="חפש פוסטר...">
        </div>
        <ol id="poster-list">
          <?php foreach ($all_posters_for_list as $i => $p): ?>
            <li>
              <a href="poster.php?id=<?= $p['id'] ?>" style="color:#007bff;">
                <?= htmlspecialchars($p['title_en']) ?>
              </a>
              <?php if (!empty($p['title_he'])): ?>
                <div class="title-he"><?= htmlspecialchars($p['title_he']) ?></div>
              <?php endif; ?>
              <?php if (!empty($p['year'])): ?>
                <div class="year">שנה: <?= htmlspecialchars($p['year']) ?></div>
              <?php endif; ?>
              <?php if (!empty($p['imdb_id'])): ?>
                <div class="imdb-id">
                  <a href="https://www.imdb.com/title/<?= htmlspecialchars($p['imdb_id']) ?>" target="_blank">
                    <?= htmlspecialchars($p['imdb_id']) ?>
                  </a>
                </div>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ol>
      </div>
    </div>
  <?php else: ?>
    <div class="message">אין פוסטרים באוסף זה</div>
  <?php endif; ?>

  <div class="form-box">
    <h3>➕ הוספת פוסטרים לפי מזהים</h3>
    <form method="post" action="add_to_collection_batch.php">
      <input type="hidden" name="collection_id" value="<?= $collection['id'] ?>">
      <label>🔗 מזהים (ID רגיל או IMDb: tt...)</label>
      <textarea name="poster_ids_raw" rows="6" placeholder="לדוגמה:
45
tt1375666
89"></textarea>
      <button type="submit">📥 קשר פוסטרים</button>
    </form>
  </div>

  <?php if ($total_pages > 1): ?>
    <div style="text-align:center; margin-top:20px;">
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="collection.php?id=<?= $id ?>&page=<?= $i ?><?= $keyword ? '&q=' . urlencode($keyword) : '' ?>"
           style="margin:0 6px; padding:6px 10px; background:#eee; border-radius:4px; text-decoration:none; <?= $i==$page ? 'font-weight:bold;' : '' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</div>
<script>
  function toggleDelete() {
    document.querySelectorAll('.delete-box').forEach(el => {
      el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
    });
  }
  function setSize(size) {
    localStorage.setItem('posterSize', size);
    document.querySelectorAll('.poster-item').forEach(item => {
      item.classList.remove('small', 'medium', 'large');
      item.classList.add(size);
    });
    const grid = document.querySelector('.poster-grid');
    grid.classList.remove('small', 'medium', 'large');
    grid.classList.add(size);
    // עדכון active לכפתורים
    ['small', 'medium', 'large'].forEach(function(sz){
      document.getElementById('size-'+sz).classList.remove('active');
    });
    document.getElementById('size-'+size).classList.add('active');
  }
  window.addEventListener('DOMContentLoaded', () => {
    const savedSize = localStorage.getItem('posterSize') || 'medium';
    setSize(savedSize);
  });
  // חיפוש רשימה שמית (צד לקוח)
  const searchInput = document.getElementById('poster-search');
  if (searchInput) {
    const listItems = document.querySelectorAll('#poster-list li');
    searchInput.addEventListener('input', () => {
      const val = searchInput.value.toLowerCase();
      listItems.forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(val) ? 'list-item' : 'none';
      });
    });
  }
  // הצג/הסתר רשימה שמית
  function toggleNameList() {
    var sidebar = document.getElementById('name-list-sidebar');
    sidebar.classList.toggle('hide-list');
  }
</script>
</body>
</html>
<?php include 'footer.php'; ?>
<?php $conn->close(); ?>
