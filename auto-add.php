<?php
include 'header.php';
require_once 'server.php';
require_once 'imdb.class.php';

set_time_limit(3000000);

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
        $omdb = @json_decode(file_get_contents("http://www.omdbapi.com/?i=$imdb_id&apikey=$omdb_key&plot=full"), true);
        $title_en = $omdb['Title'] ?? '';

        // TMDb - שליפה אחת בלבד!
        $tmdb = @json_decode(file_get_contents("https://api.themoviedb.org/3/find/$imdb_id?api_key=$tmdb_key&external_source=imdb_id"), true);

        // original_title לפי TMDb
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
        $imdb_rating = !empty($omdb['imdbRating']) && is_numeric($omdb['imdbRating']) ? (float)$omdb['imdbRating'] : null;

        // כל הז'אנרים מכל המקורות
        $genres_omdb = [];
        if (!empty($omdb['Genre'])) {
            foreach (explode(',', $omdb['Genre']) as $g) {
                $g = trim($g);
                if ($g !== '') $genres_omdb[] = $g;
            }
        }

        // TMDb - רשימות ז'אנרים מלאות (עברית)
        $genres_tmdb = [];
        $tmdb_movie_genres_list = @json_decode(file_get_contents("https://api.themoviedb.org/3/genre/movie/list?api_key=$tmdb_key&language=he"), true);
        $tmdb_tv_genres_list = @json_decode(file_get_contents("https://api.themoviedb.org/3/genre/tv/list?api_key=$tmdb_key&language=he"), true);
        $movie_id2name = [];
        $tv_id2name = [];
        if (isset($tmdb_movie_genres_list['genres'])) {
            foreach ($tmdb_movie_genres_list['genres'] as $row) {
                $movie_id2name[$row['id']] = $row['name'];
            }
        }
        if (isset($tmdb_tv_genres_list['genres'])) {
            foreach ($tmdb_tv_genres_list['genres'] as $row) {
                $tv_id2name[$row['id']] = $row['name'];
            }
        }

        if (!empty($tmdb['movie_results'][0]['genre_ids'])) {
            foreach ($tmdb['movie_results'][0]['genre_ids'] as $gid) {
                if (isset($movie_id2name[$gid])) $genres_tmdb[] = $movie_id2name[$gid];
            }
        } elseif (!empty($tmdb['tv_results'][0]['genre_ids'])) {
            foreach ($tmdb['tv_results'][0]['genre_ids'] as $gid) {
                if (isset($tv_id2name[$gid])) $genres_tmdb[] = $tv_id2name[$gid];
            }
        }

        $all_genres = array_merge($genres_omdb, $genres_tmdb);

        // תרגום/מיזוג
        $genre_translate = [
            'Action' => 'אקשן', 'Adventure' => 'הרפתקאות', 'Animation' => 'אנימציה', 'Biography' => 'ביוגרפיה', 'Comedy' => 'קומדיה',
            'Crime' => 'פשע', 'Documentary' => 'דוקומנטרי', 'Drama' => 'דרמה', 'Family' => 'משפחה', 'Fantasy' => 'פנטזיה',
            'History' => 'היסטוריה', 'Horror' => 'אימה', 'Music' => 'מוזיקה', 'Musical' => 'מחזמר', 'Mystery' => 'מסתורין',
            'Romance' => 'רומנטיקה', 'Sci-Fi' => 'מדע בדיוני', 'Sport' => 'ספורט', 'Thriller' => 'מותחן', 'War' => 'מלחמה', 'Western' => 'מערבון'
        ];
        $genres_he = [];
        foreach ($all_genres as $g) {
            $g = trim($g);
            if (!$g) continue;
            if (isset($genre_translate[$g])) {
                $genres_he[] = $genre_translate[$g];
            } else {
                $genres_he[] = $g;
            }
        }
        $genres = implode(', ', array_unique($genres_he));

        $ratings = [];
        foreach ($omdb['Ratings'] ?? [] as $r) {
            $ratings[$r['Source']] = $r['Value'];
        }
        $metacritic_score = $ratings['Metacritic'] ?? '';
        $rt_score = $ratings['Rotten Tomatoes'] ?? '';

        // TMDb בעברית
        $tmdb_he = @json_decode(file_get_contents("https://api.themoviedb.org/3/find/$imdb_id?api_key=$tmdb_key&external_source=imdb_id&language=he"), true);

        $title_he = '';
        $plot_he = '';
        $poster = '';
        $poster_type_code = 'movie';
        $actors = '';
        $tvdb_id = null;

        // שדות סדרה
        $network = '';
        $network_logo = '';
        $seasons_count = 0;
        $episodes_count = 0;

        if (!empty($tmdb['tv_results'])) {
            $tv = $tmdb['tv_results'][0];
            $tv_id = $tv['id'] ?? null;
            if ($tv_id) {
                $tv_details = @json_decode(file_get_contents("https://api.themoviedb.org/3/tv/$tv_id?api_key=$tmdb_key&language=he"), true);
                if (!empty($tv_details['networks'][0]['name'])) {
                    $network = $tv_details['networks'][0]['name'];
                    $network_logo = $tv_details['networks'][0]['logo_path'] ?? '';
                    if ($network_logo) {
                        $network_logo = 'https://image.tmdb.org/t/p/w92' . $network_logo;
                    }
                }
                $seasons_count = $tv_details['number_of_seasons'] ?? 0;
                $episodes_count = $tv_details['number_of_episodes'] ?? 0;
            }
        }

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
        $actors = '';
        $producers = '';
        $cinematographers = '';
        $composers = '';
        if ($tmdbID) {
            $credits = @json_decode(file_get_contents("https://api.themoviedb.org/3/movie/$tmdbID/credits?api_key=$tmdb_key"), true);
            $cast = array_map(fn($c) => $c['name'], array_slice($credits['cast'] ?? [], 0, 10));
            $actors = implode(', ', $cast);

            $producers_arr = [];
            $cinematographers_arr = [];
            $composers_arr = [];
            foreach ($credits['crew'] ?? [] as $c) {
                if ($c['job'] === "Producer") $producers_arr[] = $c['name'];
                if ($c['job'] === "Director of Photography") $cinematographers_arr[] = $c['name'];
                if ($c['job'] === "Original Music Composer") $composers_arr[] = $c['name'];
            }
            $producers = implode(', ', array_unique($producers_arr));
            $cinematographers = implode(', ', array_unique($cinematographers_arr));
            $composers = implode(', ', array_unique($composers_arr));
        }

        // משך זמן (runtime)
        $runtime = 0;
        if (!empty($omdb['Runtime']) && preg_match('/(\d+)/', $omdb['Runtime'], $m)) {
            $runtime = (int)$m[1];
        }

        // במאים, תסריטאים, שפות, מדינות (OMDb)
        $directors = $omdb['Director'] ?? '';
        $writers = $omdb['Writer'] ?? '';
        $languages = $omdb['Language'] ?? '';
        $countries = $omdb['Country'] ?? '';

        // tmdb_collection_id (TMDb API נוסף)
        $tmdb_collection_id = null;
        if (!empty($tmdb['movie_results'][0]['id'])) {
            $tmdb_movie_id = $tmdb['movie_results'][0]['id'];
            $tmdb_details = @json_decode(file_get_contents("https://api.themoviedb.org/3/movie/$tmdb_movie_id?api_key=$tmdb_key&language=he"), true);
            if (!empty($tmdb_details['belongs_to_collection']['id'])) {
                $tmdb_collection_id = $tmdb_details['belongs_to_collection']['id'];
            }
        }

        $imdb_link = "https://www.imdb.com/title/$imdb_id";
        $metacritic_link = '';
        $rt_link = '';
        $pending = 0;
        $collection_name = null;
        $created_at = date('Y-m-d H:i:s');

        // שמירה (35 עמודות, 35 ערכים — imdb_id במקום הנכון, string!)
        $stmt = $conn->prepare("INSERT INTO posters (
            title_en, title_he, year, imdb_rating, imdb_link, network,
            image_url, plot, plot_he, lang_code, tvdb_id, youtube_trailer, genre,
            actors, metacritic_score, rt_score, metacritic_link, rt_link,
            imdb_id, pending, collection_name, created_at, type_id, directors, writers, producers,
            cinematographers, composers, runtime, languages, countries, tmdb_collection_id,
            seasons_count, episodes_count, network_logo
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )");

        $stmt->bind_param(
            'ssssssssssssssssssisssssssssssssiis',
            $title_en, $title_he, $year, $imdb_rating, $imdb_link, $network,
            $poster, $plot, $plot_he, $lang_code, $tvdb_id, $youtube_trailer, $genres,
            $actors, $metacritic_score, $rt_score, $metacritic_link, $rt_link,
            $imdb_id, $pending, $collection_name, $created_at, $type_id, $directors, $writers, $producers,
            $cinematographers, $composers, $runtime, $languages, $countries, $tmdb_collection_id,
            $seasons_count, $episodes_count, $network_logo
        );
        $stmt->execute();
        $stmt->close();

        $report[] = [
            'id' => $imdb_id,
            'status' => 'added',
            'title' => $title_en,
            'network' => $network,
            'seasons_count' => $seasons_count,
            'episodes_count' => $episodes_count
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>📥 הוספת פוסטרים מלאה</title>
  <style>
    body { font-family: 'Alef', Arial, sans-serif; background: #fafaff; margin: 0; padding: 0; }
    h2 { color: #333; margin-top: 30px; }
    form { margin: 30px 0 0 0; text-align: center; }
    textarea[name=imdb_ids] {
      width: 380px; min-height: 90px; border-radius: 8px; border: 1.5px solid #bbb;
      font-size: 17px; padding: 12px; margin-bottom: 15px; background: #fff;
      box-shadow: 0 2px 8px #0001;
      direction: ltr; resize: vertical;
      transition: border .2s;
    }
    textarea[name=imdb_ids]:focus { border-color: #028ad1; outline: none; }
    button[type=submit] {
      background: linear-gradient(90deg, #0a8fff 40%, #36e0ff 100%);
      color: #fff; font-size: 20px; font-weight: bold; padding: 10px 38px;
      border: none; border-radius: 10px; box-shadow: 0 2px 6px #0af3;
      cursor: pointer; letter-spacing: 1px; margin-bottom: 20px; transition: background .2s;
    }
    button[type=submit]:hover { background: linear-gradient(90deg, #028ad1 0%, #0de0ff 100%); }
    hr { margin: 25px 0; }
    p { margin: 12px 0 20px 0; font-size: 16px; background: #fff; display: inline-block; padding: 10px 18px; border-radius: 7px; box-shadow: 0 1px 6px #0001; }
  </style>
</head>
<body>
  <h2>📥 הוספת פוסטרים אוטומטית לפי IMDb</h2>
  <form method="post">
    <textarea name="imdb_ids" rows="10" placeholder="הכנס IMDb ID או קישורים בשורות נפרדות"></textarea><br>
    <button type="submit">🚀 הוסף</button>
  </form>
  <hr>
  <?php foreach ($report as $r): ?>
    <p>
      <?= safe($r['id']) ?> — <?= safe($r['status']) ?>
      <?php if (isset($r['title'])): ?>
        — <?= safe($r['title']) ?>
      <?php endif; ?>
      <?php if (isset($r['network']) && $r['network']): ?>
        <br>רשת: <?= safe($r['network']) ?>
      <?php endif; ?>
      <?php if (isset($r['seasons_count']) && $r['seasons_count']): ?>
        <br>עונות: <?= intval($r['seasons_count']) ?>
      <?php endif; ?>
      <?php if (isset($r['episodes_count']) && $r['episodes_count']): ?>
        <br>פרקים: <?= intval($r['episodes_count']) ?>
      <?php endif; ?>
    </p>
  <?php endforeach; ?>
</body>
</html>
<?php include 'footer.php'; ?>
