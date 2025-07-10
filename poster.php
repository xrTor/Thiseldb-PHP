<?php
include 'header.php';

function extractYoutubeId($url) {
  if (preg_match('/(?:v=|\/embed\/|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
    return $matches[1];
  }
  return '';
}

$conn = new mysqli('localhost', 'root', '123456', 'media');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$result = $conn->query("SELECT * FROM posters WHERE id = $id");
if ($result->num_rows == 0) {
  echo "<p style='text-align:center;'>😢 פוסטר לא נמצא</p>";
  exit;
}
$row = $result->fetch_assoc();
$languages = include 'languages.php';
?>

<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($row['title_en']) ?></title>
  <link rel="stylesheet" href="style.css">
  <style>
    body.rtl { direction: rtl; font-family: Arial; }
    .poster-page { max-width:800px; margin:30px auto; background:#f9f9f9 url('wbg.png'); padding:20px; border-radius:6px; box-shadow:0 0 8px rgba(0,0,0,0.1); }
    .poster-image { width:200px; float:right; margin-left:20px; border-radius:6px; box-shadow:0 0 4px rgba(0,0,0,0.08); }
    .poster-details { overflow:hidden; }
    .tag { background:#eee; padding:6px 12px; margin:4px; display:inline-block; border-radius:12px; font-size:13px; }
    .actions a { margin:0 10px; color:#007bff; text-decoration:none; }
    .actions a:hover { text-decoration:underline; }
  </style>
</head>
<body class="rtl">

<div class="poster-page">
  <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="Poster" class="poster-image">
  <div class="poster-details">
    <h2>
      <?= htmlspecialchars($row['title_en']) ?>
      <?php if (!empty($row['title_he'])): ?><br><?= htmlspecialchars($row['title_he']) ?><?php endif; ?>
    </h2>

    <?php if ($row['is_dubbed'] || $row['has_subtitles']): ?>
      <p>
        <?php if ($row['is_dubbed']): ?>🎙️ מדובב<br><?php endif; ?>
        <?php if ($row['has_subtitles']): ?>📝 כולל כתוביות<?php endif; ?>
      </p>
    <?php endif; ?>

    <p><strong>🗓️ שנה:</strong> <?= htmlspecialchars($row['year']) ?></p>
    <p><strong>🎞️ סוג:</strong> <?= $row['type'] === 'series' ? 'סדרה' : 'סרט' ?></p>
    <p><strong>⭐ IMDb:</strong> <?= $row['imdb_rating'] ? htmlspecialchars($row['imdb_rating']) . ' / 10' : 'לא זמין' ?></p>
    <p><strong>🔤 IMDb ID:</strong> <?= htmlspecialchars($row['imdb_id']) ?></p>
    <?php if (!empty($row['tvdb_id'])): ?>
      <p><strong>🛰️ TVDB ID:</strong> <?= htmlspecialchars($row['tvdb_id']) ?></p>
    <?php endif; ?>

    <p><strong>🌐 שפת מקור:</strong><br>
      <?php
      $lang_result = $conn->query("SELECT lang_code FROM poster_languages WHERE poster_id = $id");
      if ($lang_result->num_rows > 0)
        while ($l = $lang_result->fetch_assoc())
          echo "<span class='tag'>" . htmlspecialchars($l['lang_code']) . "</span> ";
      else echo "<span style='color:#999;'>אין שפות נוספות</span>";
      ?>
    </p>
    <?php if ($row['genre']):
      $genres = explode(',', $row['genre']);
      echo "<p><strong>🎭 ז'אנר:</strong><br>";
      foreach ($genres as $g) {
        $g_clean = trim($g);
        echo "<a href='genre.php?name=" . urlencode($g_clean) . "' class='tag'>" . htmlspecialchars($g_clean) . "</a> ";
      }
      echo "</p>";
    endif; ?>

    <?php if ($row['actors']):
      $actors = explode(',', $row['actors']);
      echo "<p><strong>👥 שחקנים:</strong><br>";
      foreach ($actors as $a) {
        $a_clean = trim($a);
        echo "<a href='actor.php?name=" . urlencode($a_clean) . "' class='tag'>" . htmlspecialchars($a_clean) . "</a> ";
      }
      echo "</p>";
    endif; ?>

    <?php if ($row['imdb_link']): ?>
      <p><strong>🔗 IMDb:</strong> <a href="<?= htmlspecialchars($row['imdb_link']) ?>" target="_blank">מעבר לקישור</a></p>
    <?php endif; ?>

    <?php if ($row['metacritic_score']): ?>
      <p><strong>📊 Metacritic:</strong> <?= htmlspecialchars($row['metacritic_score']) ?></p>
    <?php endif; ?>
    <?php if ($row['rt_score']): ?>
      <p><strong>🍅 Rotten Tomatoes:</strong> <?= htmlspecialchars($row['rt_score']) ?></p>
    <?php endif; ?>
    <?php if ($row['metacritic_link']): ?>
      <p><strong>🔗 Metacritic:</strong> <a href="<?= htmlspecialchars($row['metacritic_link']) ?>" target="_blank">מעבר</a></p>
    <?php endif; ?>
    <?php if ($row['rt_link']): ?>
      <p><strong>🔗 RT:</strong> <a href="<?= htmlspecialchars($row['rt_link']) ?>" target="_blank">מעבר</a></p>
    <?php endif; ?>
<!-- 
    <div class="poster-tags">
      <strong>🏷️ תגיות:</strong><br>
       --><?php 
       /*
      $cat_result = $conn->query("SELECT c.name FROM categories c JOIN poster_categories pc ON c.id = pc.category_id WHERE pc.poster_id = $id");
      if ($cat_result->num_rows > 0)
        while ($cat = $cat_result->fetch_assoc())
          echo "<span class='tag'>" . htmlspecialchars($cat['name']) . "</span> ";
      else echo "<span style='color:#999;'>אין תגיות</span>";
     </div> 
        */ ?>
 
 
    <?php
    $video_id = extractYoutubeId($row['youtube_trailer'] ?? '');
    ?>
 <!--    <pre>🎥 קישור טריילר: <?= htmlspecialchars($row['youtube_trailer']) ?></pre>
    <pre>🎬 מזהה שנשלף: <?= htmlspecialchars($video_id) ?></pre>
    -->
    <?php if ($video_id): ?>
      <div style="margin-top:30px; text-align:center;">
        <h3>🎞️ טריילר</h3>
        <iframe width="100%" height="315"
          src="https://www.youtube.com/embed/<?= htmlspecialchars($video_id) ?>"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen loading="lazy"></iframe>
      </div>
    <?php else: ?>
      <div style="margin-top:30px; text-align:center; color:#888;">
        <h3>🎞️ טריילר</h3>
        <p>אין טריילר זמין כרגע 😢</p>
      </div>
    <?php endif; ?>

    <div class="actions">
      <a href="edit.php?id=<?= $row['id'] ?>">✏️ ערוך</a> |
      <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('למחוק את הפוסטר?')">🗑️ מחק</a> |
      <a href="index.php">⬅ חזרה</a>
    </div>

  </div>
</div>

</body>
</html>

<?php
$conn->close();
include 'footer.php';
?>
