<?php
include 'header.php';
require_once 'server.php';
require_once 'imdb.class.php';

set_time_limit(3000); // עד 50 דקות

function safe($str) {
  return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

$tmdb_key = '931b94936ba364daf0fd91fb38ecd91e';
$omdb_key = '1ae9a12e';
$report = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['imdb_ids'])) {
  $ids = preg_split('/[\n\r]+/', $_POST['imdb_ids']);

  foreach ($ids as $raw) {
    $raw = trim($raw);
    if ($raw === '') continue;

    if (preg_match('/tt\d+/', $raw, $match)) {
      $imdb_id = $match[0];
    } else {
      $report[] = ['id' => $raw, 'status' => 'invalid'];
      continue;
    }

    // בדיקה במסד
    $stmt = $conn->prepare("SELECT id FROM posters WHERE imdb_id = ?");
    $stmt->bind_param("s", $imdb_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
      $report[] = ['id' => $imdb_id, 'status' => 'exists'];
      $stmt->close();
      continue;
    }
    $stmt->close();

    // OMDb
    $omdb = json_decode(file_get_contents("http://www.omdbapi.com/?i=$imdb_id&apikey=$omdb_key&plot=full"), true);
    $title_en = $omdb['Title'] ?? '';

    $original_title = '';
    if (!empty($tmdb['movie_results'][0]['original_title'])) {
      $original_title = $tmdb['movie_results'][0]['original_title'];
    } elseif (!empty($tmdb['tv_results'][0]['original_name'])) {
      $original_title = $tmdb['tv_results'][0]['original_name'];
    }

    if ($original_title && $original_title !== $title_en) {
      $title_en = "$original_title AKA $title_en";
    }

    $original_title = '';
    if (!empty($tmdb['movie_results'][0]['original_title'])) {
      $original_title = $tmdb['movie_results'][0]['original_title'];
    } elseif (!empty($tmdb['tv_results'][0]['original_name'])) {
      $original_title = $tmdb['tv_results'][0]['original_name'];
    }

    if ($original_title && $original_title !== $title_en) {
      $title_en = "$original_title AKA $title_en";
    }

    $year = $omdb['Year'] ?? '';
    $plot = $omdb['Plot'] ?? '';
    $lang_code = strtolower(substr($omdb['Language'] ?? 'en', 0, 2));
    $imdb_rating = $omdb['imdbRating'] ?? '';
    $genre_translate = [
  'Action' => 'אקשן',
  'Adventure' => 'הרפתקאות',
  'Animation' => 'אנימציה',
  'Biography' => 'ביוגרפיה',
  'Comedy' => 'קומדיה',
  'Crime' => 'פשע',
  'Documentary' => 'דוקומנטרי',
  'Drama' => 'דרמה',
  'Family' => 'משפחה',
  'Fantasy' => 'פנטזיה',
  'History' => 'היסטוריה',
  'Horror' => 'אימה',
  'Music' => 'מוזיקה',
  'Musical' => 'מחזמר',
  'Mystery' => 'מסתורין',
  'Romance' => 'רומנטיקה',
  'Sci-Fi' => 'מדע בדיוני',
  'Sport' => 'ספורט',
  'Thriller' => 'מותחן',
  'War' => 'מלחמה',
  'Western' => 'מערבון'
];

$genres_en = array_map('trim', explode(',', $omdb['Genre'] ?? ''));
$genres_he = [];
foreach ($genres_en as $genre) {
    if (isset($genre_translate[$genre])) {
        $genres_he[] = $genre_translate[$genre];
    } else {
        $genres_he[] = $genre;
    }
}


    $ratings = [];
    foreach ($omdb['Ratings'] ?? [] as $r) {
      $ratings[$r['Source']] = $r['Value'];
    }
    $metacritic_score = $ratings['Metacritic'] ?? '';
    $rt_score = $ratings['Rotten Tomatoes'] ?? '';

    // TMDb
    $tmdb = $tmdb = json_decode(file_get_contents("https://api.themoviedb.org/3/find/$imdb_id?api_key=$tmdb_key&external_source=imdb_id"), true);

$original_title = '';
if (!empty($tmdb['movie_results'][0]['original_title'])) {
  $original_title = $tmdb['movie_results'][0]['original_title'];
} else if (!empty($tmdb['tv_results'][0]['original_name'])) {
  $original_title = $tmdb['tv_results'][0]['original_name'];
}

if ($original_title && $original_title !== $title_en) {
  $title_en = "$original_title AKA $title_en";
}
    $tmdb_he = json_decode(file_get_contents("https://api.themoviedb.org/3/find/$imdb_id?api_key=$tmdb_key&external_source=imdb_id&language=he"), true);

    $title_he = '';
    $plot_he = '';
    $poster = '';
    $poster_type_code = 'movie';
    $actors = '';
    $tvdb_id = null;

    if (!empty($tmdb['movie_results'])) {
      $movie = $tmdb['movie_results'][0];
      $poster_he = $tmdb_he['movie_results'][0]['poster_path'] ?? '';
      $poster = $poster_he ? 'https://image.tmdb.org/t/p/w500' . $poster_he : 'https://image.tmdb.org/t/p/w500' . ($movie['poster_path'] ?? '');
      $poster_type_code = 'movie';
      $title_he = $tmdb_he['movie_results'][0]['title'] ?? '';
      $plot_he = $tmdb_he['movie_results'][0]['overview'] ?? '';
    } elseif (!empty($tmdb['tv_results'])) {
      $tv = $tmdb['tv_results'][0];
      $poster_he = $tmdb_he['tv_results'][0]['poster_path'] ?? '';
      $poster = $poster_he ? 'https://image.tmdb.org/t/p/w500' . $poster_he : 'https://image.tmdb.org/t/p/w500' . ($tv['poster_path'] ?? '');
      $poster_type_code = 'series';
      $title_he = $tmdb_he['tv_results'][0]['name'] ?? '';
      $plot_he = $tmdb_he['tv_results'][0]['overview'] ?? '';
    }

    // סוג פוסטר
    $type_stmt = $conn->prepare("SELECT id FROM poster_types WHERE code = ? LIMIT 1");
    $type_stmt->bind_param("s", $poster_type_code);
    $type_stmt->execute();
    $type_stmt->bind_result($type_id);
    $type_stmt->fetch();
    $type_stmt->close();

    // קרדיטים
    $tmdbID = $tmdb['movie_results'][0]['id'] ?? $tmdb['tv_results'][0]['id'] ?? null;
    if ($tmdbID) {
      $credits = json_decode(file_get_contents("https://api.themoviedb.org/3/movie/$tmdbID/credits?api_key=$tmdb_key"), true);
      $cast = array_map(fn($c) => $c['name'], array_slice($credits['cast'] ?? [], 0, 10));
      $actors = implode(', ', $cast);
    }

    $imdb_link = "https://www.imdb.com/title/$imdb_id";
    $metacritic_link = '';
    $rt_link = '';
    $has_subtitles = 0;
    $is_dubbed = 0;
    $youtube_trailer = '';

    $stmt = $conn->prepare("INSERT INTO posters 
      (imdb_id, title_en, title_he, year, plot, plot_he, image_url, type_id, imdb_rating, genre, actors,
      youtube_trailer, has_subtitles, is_dubbed, lang_code, tvdb_id, imdb_link, metacritic_score, rt_score, metacritic_link, rt_link)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sssissssdsssiiissssss",
      $imdb_id, $title_en, $title_he, $year, $plot, $plot_he, $poster, $type_id,
      $imdb_rating, $genres, $actors, $youtube_trailer, $has_subtitles, $is_dubbed,
      $lang_code, $tvdb_id, $imdb_link, $metacritic_score, $rt_score, $metacritic_link, $rt_link
    );
    $stmt->execute();
    $stmt->close();

    if ($title_he && stripos($title_en, $title_he) === false) {
        $title_en .= " (AKA $title_he)";
      }
      $report[] = ['id' => $imdb_id, 'status' => 'added', 'title' => $title_en];
  }
}
?>

<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>📥 הוספת פוסטרים מלאה</title>
</head>
<body>
  <h2>📥 הוספת פוסטרים אוטומטית לפי IMDb</h2>
  <form method="post">
    <textarea name="imdb_ids" rows="10" placeholder="הכנס IMDb ID או קישורים בשורות נפרדות"></textarea><br>
    <button type="submit">🚀 הוסף</button>
  </form>
  <hr>
  <?php foreach ($report as $r): ?>
    <p><?= safe($r['id']) ?> — <?= safe($r['status']) ?><?= isset($r['title']) ? ' — ' . safe($r['title']) : '' ?></p>
  <?php endforeach; ?>
</body>
</html>
