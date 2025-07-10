<?php
require_once 'header.php';
require_once 'functions.php';
require_once 'imdb.class.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli('localhost', 'root', '123456', 'media');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message = '';
$poster_id = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $title_en         = trim($_POST['title_en'] ?? '');
  $title_he         = trim($_POST['title_he'] ?? '');
  $year             = trim($_POST['year'] ?? '');
  $lang_code        = $_POST['lang_code'] ?? (count($_POST['languages'] ?? []) === 1 ? $_POST['languages'][0] : 'en');
  $imdb_id          = trim($_POST['imdb_id'] ?? '');
  $imdb_rating      = trim($_POST['imdb_rating'] ?? '');
  $imdb_link        = trim($_POST['imdb_link'] ?? '');
  $image_url        = trim($_POST['image_url'] ?? '');
  $plot             = trim($_POST['plot'] ?? '');
  $type             = $_POST['type'] ?? 'movie';
  $tvdb_id          = trim($_POST['tvdb_id'] ?? '');
  $genre            = trim($_POST['genre'] ?? '');
  $actors           = trim($_POST['actors'] ?? '');
  $youtube_trailer  = trim($_POST['youtube_trailer'] ?? '');
  $has_subtitles    = isset($_POST['has_subtitles']) ? 1 : 0;
  $is_dubbed        = isset($_POST['is_dubbed']) ? 1 : 0;
  $metacritic_score = trim($_POST['metacritic_score'] ?? '');
  $rt_score         = trim($_POST['rt_score'] ?? '');
  $metacritic_link  = trim($_POST['metacritic_link'] ?? '');
  $rt_link          = trim($_POST['rt_link'] ?? '');
  $languages_posted = $_POST['languages'] ?? [];
  $categories       = $_POST['categories'] ?? [];

  if (empty($imdb_id) && preg_match('/tt\d+/', $imdb_link, $m)) {
    $imdb_id = $m[0];
  }

  // 💡 תיקון ניקוי טריילר YouTube — רק קישור תקף יישמר
  if ($youtube_trailer === '0' || strlen($youtube_trailer) < 10 ||
      !preg_match('/(?:v=|\/embed\/|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $youtube_trailer)) {
    $youtube_trailer = '';
  }

  echo "<pre>🎥 טריילר לפני שמירה: " . htmlspecialchars($youtube_trailer) . "</pre>";

  if (!empty($imdb_id)) {
    $check = $conn->prepare("SELECT id FROM posters WHERE imdb_id = ?");
    $check->bind_param("s", $imdb_id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
      $message = "<p style='color:orange; text-align:center;'>⚠️ הפוסטר כבר קיים במסד</p>";
    }
    $check->close();
  }

  if (empty($message)) {
    $stmt = $conn->prepare("INSERT INTO posters 
      (title_en, title_he, year, imdb_rating, imdb_link, image_url, plot, type,
       tvdb_id, genre, actors, youtube_trailer, has_subtitles, is_dubbed, lang_code,
       imdb_id, metacritic_score, rt_score, metacritic_link, rt_link)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
      $message = "<p style='color:red;'>❌ שגיאה ב־prepare: " . $conn->error . "</p>";
    } else {
      $stmt->bind_param("sssssssssssiisssssss",
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
  }
}
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>📥 הוספת פוסטר</title>
  <style>
    body { font-family: Arial; text-align: center; padding: 20px; }
    form { max-width:600px; margin:auto; text-align:right; direction:rtl; }
    input, textarea, select { width:100%; margin-bottom:10px; padding:6px; }
    .language-cell { display:flex; gap:6px; align-items:center; font-size:13px; }
    .language-cell img { height:16px; }
  </style>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const imdbInput = document.querySelector("[name='imdb_link']");
      const imageUrlInput = document.querySelector("[name='image_url']");
      const posterPreview = document.getElementById("posterPreview");

      function updatePosterPreview(url) {
        if (url) {
          posterPreview.innerHTML = `
            <p style="margin-bottom:8px; font-weight:bold;">🖼️ תצוגה מקדימה:</p>
            <img src="${url}" style="max-width:100%; border-radius:6px;">
          `;
          posterPreview.style.display = "block";
        } else {
          posterPreview.innerHTML = '';
          posterPreview.style.display = "none";
        }
      }

      function fetchDetails(imdbId) {
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
              document.querySelector("[name='imdb_id']").value      = imdbId;
              updatePosterPreview(data.Poster || '');
            } else {
              alert("❌ IMDb לא החזיר תוצאה תקפה");
            }
          })
          .catch(() => alert("❌ שגיאה בחיבור ל־OMDb"));
      }

      const imdbMatch = imdbInput.value.trim().match(/tt\d+/);
      if (imdbMatch) fetchDetails(imdbMatch[0]);

      imageUrlInput.addEventListener("input", () => updatePosterPreview(imageUrlInput.value.trim()));
      updatePosterPreview(imageUrlInput.value.trim());

      window.fetchFromIMDb = function () {
        const match = imdbInput.value.trim().match(/tt\d+/);
        if (!match) {
          alert("❌ קישור IMDb לא תקף");
          return;
        }
        fetchDetails(match[0]);
      };
    });
  </script>
</head>
<body>

<h2>📥 הוספת פוסטר חדש</h2>
<?= $message ?>

<form method="post" action="add.php">
  <label>🔗 קישור ל־IMDb:</label>
  <input type="text" name="imdb_link" value="<?= htmlspecialchars($imdb_link ?? '') ?>">
  <button type="button" onclick="fetchFromIMDb()">🕵️‍♂️ שלוף פרטים</button><br>

  <label>כותרת באנגלית:</label><input type="text" name="title_en">
  <label>כותרת בעברית:</label><input type="text" name="title_he">
  <label>🗓️ שנה:</label><input type="text" name="year">
  <label>🎯 דירוג IMDb:</label><input type="text" name="imdb_rating">
  <label>🖼️ כתובת תמונה:</label><input type="text" name="image_url">
  <div id="posterPreview" style="margin:10px 0; padding:15px; border:1px solid #ccc; border-radius:6px; background:#f8f8f8; display:none;"></div>

  <label>📘 תקציר:</label><textarea name="plot" rows="3"></textarea>
  <label>🎭 ז'אנר:</label><input type="text" name="genre">
  <label>👥 שחקנים:</label><input type="text" name="actors">
  <label>🔗 TVDB ID:</label><input type="text" name="tvdb_id">
  <label>🎞️ טריילר YouTube:</label><input type="text" name="youtube_trailer">
  <label>סוג:</label>
  <select name="type">
    <option value="movie">סרט</option>
    <option value="series">סדרה</option>
  </select>
  <label>📝 כתוביות:</label><input type="checkbox" name="has_subtitles" value="1">
  <label>🎙️ דיבוב:</label><input type="checkbox" name="is_dubbed" value="1">
  <label>📊 Metacritic:</label><input type="text" name="metacritic_score">
  <label>🍅 Rotten Tomatoes:</label><input type="text" name="rt_score">
  <label>🔗 קישור Metacritic:</label><input type="text" name="metacritic_link">
  <label>🔗 קישור RT:</label><input type="text" name="rt_link">
  <label>🔤 IMDb ID:</label><input type="text" name="imdb_id">

  <div style="text-align:left;"><?php include 'flags.php'; ?></div>
  <button type="submit">💾 שמור פוסטר</button>
</form>

<?php
$conn->close();
include 'footer.php';
?>
</body>
</html>
