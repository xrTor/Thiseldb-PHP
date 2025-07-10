<?php
require_once 'header.php';
require_once 'functions.php';
require_once 'imdb.class.php';

$conn = new mysqli('localhost', 'root', '123456', 'media');
if ($conn->connect_error) die("Connection failed");

$message = '';
$poster_id = 0;
$languages = include 'languages.php'; // מחזיר מערך של שפות כולל דגלים

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $title_en         = $_POST['title_en'] ?? '';
  $title_he         = $_POST['title_he'] ?? '';
  $year             = $_POST['year'] ?? '';
  $lang_code        = $_POST['lang_code'] ?? (count($_POST['languages'] ?? []) === 1 ? $_POST['languages'][0] : '');
  $imdb_id          = $_POST['imdb_id'] ?? '';
  $imdb_rating      = $_POST['imdb_rating'] ?? '';
  $imdb_link        = $_POST['imdb_link'] ?? '';
  $image_url        = $_POST['image_url'] ?? '';
  $plot             = $_POST['plot'] ?? '';
  $type             = $_POST['type'] ?? 'movie';
  $tvdb_id          = $_POST['tvdb_id'] ?? '';
  $genre            = $_POST['genre'] ?? '';
  $actors           = $_POST['actors'] ?? '';
  $youtube_trailer  = $_POST['youtube_trailer'] ?? '';
  $has_subtitles    = isset($_POST['has_subtitles']) ? 1 : 0;
  $is_dubbed        = isset($_POST['is_dubbed']) ? 1 : 0;
  $metacritic_score = $_POST['metacritic_score'] ?? '';
  $rt_score         = $_POST['rt_score'] ?? '';
  $metacritic_link  = $_POST['metacritic_link'] ?? '';
  $rt_link          = $_POST['rt_link'] ?? '';
  $languages_posted = $_POST['languages'] ?? [];
  $categories       = $_POST['categories'] ?? [];

  if ($youtube_trailer === '0') $youtube_trailer = '';

  $check = $conn->prepare("SELECT id FROM posters WHERE imdb_link = ?");
  $check->bind_param("s", $imdb_link);
  $check->execute();
  $check->store_result();

  if ($check->num_rows > 0) {
    $message = "<p style='color:orange; text-align:center;'>⚠️ הפוסטר כבר קיים במסד</p>";
  } else {
    $stmt = $conn->prepare("INSERT INTO posters 
    (title_en, title_he, year, imdb_rating, imdb_link, image_url, plot, type,
     tvdb_id, genre, actors, youtube_trailer, has_subtitles, is_dubbed, lang_code,
     imdb_id, metacritic_score, rt_score, metacritic_link, rt_link)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
      echo "<p style='color:red;'>❌ שגיאה ב־prepare: " . $conn->error . "</p>";
      exit;
    }

    $stmt->bind_param("sssssssssssiiisssssss",
      $title_en, $title_he, $year, $imdb_rating, $imdb_link, $image_url, $plot,
      $type, $tvdb_id, $genre, $actors, $youtube_trailer,
      $has_subtitles, $is_dubbed, $lang_code,
      $imdb_id, $metacritic_score, $rt_score, $metacritic_link, $rt_link
    );

    $stmt->execute();

    if ($stmt->affected_rows > 0) {
      $poster_id = $conn->insert_id;
      $message = "<p style='color:green; text-align:center;'>✅ הפוסטר נשמר בהצלחה! (ID: $poster_id)</p>";

      foreach ($languages_posted as $code) {
        $lang_stmt = $conn->prepare("INSERT INTO poster_languages (poster_id, lang_code) VALUES (?, ?)");
        $lang_stmt->bind_param("is", $poster_id, $code);
        $lang_stmt->execute();
        $lang_stmt->close();
      }

      foreach ($categories as $cat_id) {
        $cat_stmt = $conn->prepare("INSERT INTO poster_categories (poster_id, category_id) VALUES (?, ?)");
        $cat_stmt->bind_param("ii", $poster_id, intval($cat_id));
        $cat_stmt->execute();
        $cat_stmt->close();
      }
    } else {
      $message = "<p style='color:red; text-align:center;'>❌ שמירת הפוסטר נכשלה: " . $stmt->error . "</p>";
    }

    $stmt->close();
  }

  $check->close();
}
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>📥 הוספת פוסטר</title>
  <style>
    body { font-family: Arial; text-align: center; padding: 20px; }
    input, textarea, select { width:100%; margin-bottom:10px; padding:6px; }
    form { max-width:600px; margin:auto; text-align:right; direction:rtl; }
    #previewImage { max-width:250px; display:none; border-radius:6px; margin:auto; }
    #previewPlot  { margin-top:10px; font-size:14px; line-height:1.6; text-align:center; }

    .languages-table {
      font-family: calibri;
      border-collapse: collapse;
      margin: 10px auto;
      direction: ltr;
    }
    .language-td {
      padding: 6px;
      text-align: left;
      vertical-align: middle;
    }
    .language-cell {
      display: flex;
      align-items: center;
      justify-content: flex-start;
      gap: 6px;
      font-size: 13px;
    }
    .language-cell input[type="checkbox"] {
      transform: scale(1.2);
      cursor: pointer;
      order: 0;
    }
    .language-cell img {
      height: 16px;
      order: 1;
    }
    .language-cell span {
      order: 2;
      flex-grow: 1;
    }
  </style>
  <script>
    function fetchFromIMDb() {
      const url = document.querySelector("[name='imdb_link']").value.trim();
      const match = url.match(/tt\d+/);
      if (!match) return;
      const imdbId = match[0];
      const apiKey = '1ae9a12e';

      fetch(`https://www.omdbapi.com/?i=${imdbId}&apikey=${apiKey}`)
        .then(res => res.json())
        .then(data => {
          if (data.Response === "True") {
            document.querySelector("[name='title_en']").value     = data.Title || '';
            document.querySelector("[name='year']").value         = data.Year || '';
            document.querySelector("[name='imdb_rating']").value  = data.imdbRating || '';
            document.querySelector("[name='image_url']").value    = data.Poster || '';
            document.querySelector("[name='plot']").value         = data.Plot || '';
            document.querySelector("[name='genre']").value        = data.Genre || '';
            document.querySelector("[name='actors']").value       = data.Actors || '';
            document.getElementById('previewPlot').textContent    = data.Plot || '';
            if (data.Poster) {
              document.getElementById('previewImage').src = data.Poster;
              document.getElementById('previewImage').style.display = 'block';
            }
          }
        });
    }
  </script>
</head>
<body>

<h2>📥 הוספת פוסטר חדש</h2>
<?= $message ?>

<form method="post" action="add.php">
  <label>🔗 קישור ל־IMDb:</label>
  <input type="text" name="imdb_link">
  <button type="button" onclick="fetchFromIMDb()">🕵️‍♂️ שלוף פרטים</button>

  <label>כותרת באנגלית:</label><input type="text" name="title_en">
  <label>כותרת בעברית:</label><input type="text" name="title_he">
  <label>🗓️ שנה:</label><input type="text" name="year">
  <label>🎯 דירוג IMDb:</label><input type="text" name="imdb_rating">
  <label>🖼️ כתובת תמונה:</label><input type="text" name="image_url">
  <label>📘 תקציר:</label><textarea name="plot" rows="4"></textarea>
  <label>🎭 ז'אנר:</label><input type="text" name="genre">
  <label>👥 שחקנים:</label><input type="text" name="actors">
  <label>🔗 TVDB ID:</label><input type="text" name="tvdb_id">
  <label>🎞️ קישור לטריילר YouTube:</label><input type="text" name="youtube_trailer">
  <label>סוג:</label><select name="type"><option value="movie">סרט</option><option value="series">סדרה</option></select>
  <label>📝 כתוביות:</label><input type="checkbox" name="has_subtitles" value="1">
  <label>🎙️ דיבוב:</label><input type="checkbox" name="is_dubbed" value="1">
  <label>📊 Metacritic:</label><input type="text" name="metacritic_score">
  <label>🍅 Rotten Tomatoes:</label><input type="text" name="rt_score">
  <label>📊 קישור ל־Metacritic:</label><input type="text" name="metacritic_link">
  <label>🍅 קישור ל־Rotten Tomatoes:</label><input type="text" name="rt_link">
  <label>🔤 קוד IMDb:</label><input type="text" name="imdb_id">

  <label>🌐 שפה ראשית:</label>
  <select name="lang_code">
    <?php foreach ($languages as $lang): ?>
      <option value="<?= $lang['code'] ?>"><?= htmlspecialchars($lang['label']) ?></option>
    <?php endforeach; ?>
  </select>

  <label>🌐 שפות מקור:</label>
  <div id="languageMenu">
    <?php include 'flags.php'; ?>
  </div>

  <label>🏷️ קטגוריות:</label>
  <select name="categories[]" multiple>
    <?php
    $cat_result = $conn->query("SELECT * FROM categories");
    while ($cat = $cat_result->fetch_assoc()):
    ?>
      <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
    <?php endwhile; ?>
  </select>

  <button type="submit">💾 שמור פוסטר</button>
</form>

<div id="preview" style="margin-top:20px;">
  <img id="previewImage" src="" alt="">
  <div id="previewPlot"></div>
</div>

<?php if ($poster_id > 0): ?>
  <div style="margin:30px auto; padding:15px; border:1px solid #ccc; border-radius:6px; max-width:500px; text-align:right; direction:rtl;">
    <h3>📌 פרטי הפוסטר שנשמר:</h3>
    <p><strong>🎬 כותרת:</strong> <?= htmlspecialchars($title_he ?: $title_en) ?></p>
    <p><strong>🗓️ שנה:</strong> <?= htmlspecialchars($year) ?></p>
    <p><strong>🎯 דירוג IMDb:</strong> <?= htmlspecialchars($imdb_rating) ?></p>
    <p><strong>🌐 שפה ראשית:</strong> <?= htmlspecialchars($lang_code) ?>
      <?php
      foreach ($languages as $lang) {
        if ($lang['code'] === $lang_code) {
          echo "<img src='{$lang['flag']}' alt='{$lang['label']}' title='{$lang['label']}' style='height:16px; margin-right:6px; vertical-align:middle;'>";
          break;
        }
      }
      ?>
    </p>
    <?php if ($image_url): ?>
      <img src="<?= htmlspecialchars($image_url) ?>" style="max-width:100%; border-radius:6px;">
    <?php endif; ?>
    <?php if ($plot): ?>
      <p style="margin-top:10px; font-size:14px; line-height:1.6;">📘 <?= htmlspecialchars($plot) ?></p>
    <?php endif; ?>
    <br><a href="add.php" style="color:blue;">↩️ הוסף פוסטר נוסף</a>
  </div>
<?php endif; ?>

<?php include 'footer.php'; ?>
</body>
</html>
