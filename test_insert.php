<?php
require_once 'functions.php';
require_once 'languages.php'; // אם יש שימוש בטבלת שפות

$conn = new mysqli('localhost', 'root', '123456', 'media');
if ($conn->connect_error) die("Connection failed");

$title_en         = 'The Matrix';
$title_he         = 'המטריקס';
$year             = '1999';
$imdb_rating      = '8.7';
$imdb_link        = 'https://www.imdb.com/title/tt0133093/';
$imdb_id          = 'tt0133093';
$image_url        = 'https://upload.wikimedia.org/wikipedia/en/c/c1/The_Matrix_Poster.jpg';
$plot             = 'A computer hacker learns about the true nature of reality and his role in the war against its controllers.';
$type             = 'movie';
$tvdb_id          = '';
$genre            = 'Action, Sci-Fi';
$actors           = 'Keanu Reeves, Laurence Fishburne, Carrie-Anne Moss';
$youtube_trailer  = 'https://www.youtube.com/watch?v=vKQi3bBA1y8';
$has_subtitles    = 1;
$is_dubbed        = 0;
$lang_code        = 'en';
$metacritic_score = '73';
$rt_score         = '88%';
$metacritic_link  = 'https://www.metacritic.com/movie/the-matrix';
$rt_link          = 'https://www.rottentomatoes.com/m/matrix';

// לפני השאילתה — בדיקה של כל המשתנים
echo '<pre>';
var_dump([
  $title_en, $title_he, $year, $imdb_rating, $imdb_link, $image_url, $plot,
  $type, $tvdb_id, $genre, $actors, $youtube_trailer,
  $has_subtitles, $is_dubbed, $lang_code,
  $imdb_id, $metacritic_score, $rt_score, $metacritic_link, $rt_link
]);
echo '</pre>';

// ואז השאילתה

$stmt = $conn->prepare("INSERT INTO posters 
(title_en, title_he, year, imdb_rating, imdb_link, image_url, plot, type,
 tvdb_id, genre, actors, youtube_trailer, has_subtitles, is_dubbed, lang_code,
 imdb_id, metacritic_score, rt_score, metacritic_link, rt_link)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
  die("❌ prepare failed: " . $conn->error);
}

$stmt->bind_param("sssssssssssiiisssssss",
  $title_en, $title_he, $year, $imdb_rating, $imdb_link, $image_url, $plot,
  $type, $tvdb_id, $genre, $actors, $youtube_trailer,
  $has_subtitles, $is_dubbed, $lang_code,
  $imdb_id, $metacritic_score, $rt_score, $metacritic_link, $rt_link
);

if (!$stmt->execute()) {
  die("❌ execute failed: " . $stmt->error);
}

echo "✅ פוסטר נוסע בהצלחה! (ID: " . $conn->insert_id . ")";
$stmt->close();
$conn->close();
?>
