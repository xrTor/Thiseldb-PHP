<?php
include 'header.php';

function extractImdbId($input) {
  if (preg_match('/tt\d{7,8}/', $input, $matches)) return $matches[0];
  return '';
}
function extractLocalId($input) {
  if (preg_match('/poster\.php\?id=(\d+)/', $input, $matches)) return (int)$matches[1];
  return 0;
}
function extractYoutubeId($url) {
  if (preg_match('/(?:v=|\/embed\/|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) return $matches[1];
  return '';
}

require_once 'server.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$result = $conn->query("SELECT * FROM posters WHERE id = $id");
if ($result->num_rows == 0) { echo "<p style='text-align:center;'>❌ פוסטר לא נמצא</p>"; exit; }
$row = $result->fetch_assoc();
$video_id = extractYoutubeId($row['youtube_trailer'] ?? '');

// דירוגים
session_start();
$visitor_token = session_id();
$user_vote = '';
$vote_row = $conn->query("SELECT vote_type FROM poster_votes WHERE poster_id = $id AND visitor_token = '$visitor_token'");
if ($vote_row->num_rows) {
  $user_vote = $vote_row->fetch_assoc()['vote_type'];
}
if (isset($_POST['vote'])) {
  $vote = $_POST['vote'];
  if ($vote === 'remove') {
    $conn->query("DELETE FROM poster_votes WHERE poster_id=$id AND visitor_token='$visitor_token'");
    $user_vote = '';
  } elseif (in_array($vote, ['like','dislike'])) {
    if ($user_vote === '') {
      $stmt = $conn->prepare("INSERT INTO poster_votes (poster_id, visitor_token, vote_type) VALUES (?, ?, ?)");
      $stmt->bind_param("iss", $id, $visitor_token, $vote);
      $stmt->execute();
      $stmt->close();
    } else {
      $stmt = $conn->prepare("UPDATE poster_votes SET vote_type=? WHERE poster_id=? AND visitor_token=?");
      $stmt->bind_param("sis", $vote, $id, $visitor_token);
      $stmt->execute();
      $stmt->close();
    }
    $user_vote = $vote;
  }
}
$likes = $conn->query("SELECT COUNT(*) as c FROM poster_votes WHERE poster_id=$id AND vote_type='like'")->fetch_assoc()['c'];
$dislikes = $conn->query("SELECT COUNT(*) as c FROM poster_votes WHERE poster_id=$id AND vote_type='dislike'")->fetch_assoc()['c'];

?><!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($row['title_en']) ?></title>
  <style>
    body { background: #f1f1f1; font-family: Arial; padding: 20px; direction: rtl; }
    .container { max-width: 960px; margin: auto; background: #fff; padding: 20px; border: 1px solid #ccc; }
    h2.title { text-align: center; margin-bottom: 10px; font-size: 24px; }
    .top-actions { text-align: center; font-size: 13px; margin-bottom: 20px; color: #555; }
    .section-box { background: #f6f6f6; border: 1px solid #ccc; padding: 10px; margin: 15px 0; }
    .info-layout { display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-start; }
    .poster-col { flex: 1; min-width: 200px; text-align: center; }
    .details-col { flex: 2; min-width: 300px; }
    .poster-col img { width: 100%; max-width: 240px; border: 1px solid #ccc; }
    .rating-icons { display: flex; gap: 20px; margin-top: 10px; align-items: center; flex-wrap: wrap; }
    .rating-icons div { display: flex; align-items: center; gap: 8px; font-weight: bold; }
    .rating-icons img { width: 32px; height: 32px; vertical-align: middle; }
    .tag { background: #eee; padding: 5px 10px; margin: 3px; display: inline-block; border-radius: 12px; font-size: 13px; color: #333; text-decoration: none; }
    .vote-buttons button { margin: 5px; padding: 8px 12px; border-radius: 6px; border: none; cursor: pointer; }
  </style>
</head>
<body>
<div class="container">
  <h2 class="title"><?= htmlspecialchars($row['title_en']) ?> [<?= $row['year'] ?>] by <?= htmlspecialchars($row['director'] ?? 'Unknown') ?></h2>
  <div class="top-actions">
    [Edit] [Delete] [Report] [Add to collection]
  </div>

  <div class="section-box">
    <?php if ($video_id): ?>
      <iframe width="100%" height="360" src="https://www.youtube.com/embed/<?= htmlspecialchars($video_id) ?>" frameborder="0" allowfullscreen></iframe>
    <?php else: ?>
      <p style="text-align:center; color:#888;">אין טריילר זמין כרגע</p>
    <?php endif; ?>
  </div>

  <div class="info-layout">
    <div class="poster-col">
      <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="poster">
      <form method="post" class="vote-buttons">
        <button name="vote" value="like" style="background:<?= $user_vote==='like' ? '#28a745' : '#ccc' ?>; color:white;">❤️ אהבתי (<?= $likes ?>)</button>
        <button name="vote" value="dislike" style="background:<?= $user_vote==='dislike' ? '#dc3545' : '#ccc' ?>; color:white;">💔 לא אהבתי (<?= $dislikes ?>)</button>
        <?php if ($user_vote): ?>
        <button name="vote" value="remove" style="background:#666; color:white;">❌ בטל הצבעה</button>
        <?php endif; ?>
      </form>
שחקנים
<!--
כאן מופיע מה שמתחת לתמונה
        -->

      <div class="section-box">
        <strong>📅 שנה:</strong> <?= $row['year'] ?><br>
        <strong>🎬 סוג:</strong> <?= $row['type'] ?><br>
        <strong>🔤 IMDb ID:</strong> <?= htmlspecialchars($row['imdb_id']) ?><br>
        <strong>🌐 שפות:</strong>
        <?php
        $lang_result = $conn->query("SELECT lang_code FROM poster_languages WHERE poster_id = $id");
        if ($lang_result->num_rows > 0):
          while ($l = $lang_result->fetch_assoc()):
            echo "<span class='tag'>" . htmlspecialchars($l['lang_code']) . "</span> ";
          endwhile;
        else:
          echo "<span style='color:#999;'>אין שפות</span>";
        endif;
        ?>
      </div>

        

    </div>
      <div class="section-box">
        <strong>📅 שנה:</strong> <?= $row['year'] ?><br>
        <strong>🎬 סוג:</strong> <?= $row['type'] ?><br>
        <strong>🔤 IMDb ID:</strong> <?= htmlspecialchars($row['imdb_id']) ?><br>
        <strong>🌐 שפות:</strong>
        <?php
        $lang_result = $conn->query("SELECT lang_code FROM poster_languages WHERE poster_id = $id");
        if ($lang_result->num_rows > 0):
          while ($l = $lang_result->fetch_assoc()):
            echo "<span class='tag'>" . htmlspecialchars($l['lang_code']) . "</span> ";
          endwhile;
        else:
          echo "<span style='color:#999;'>אין שפות</span>";
        endif;
        ?>
      </div>

      <div class="section-box rating-icons">
        <?php if (!empty($row['imdb_rating'])): ?><div><img src="imdb-icon.png"> <?= $row['imdb_rating'] ?>/10</div><?php endif; ?>
        <?php if (!empty($row['rt_score'])): ?><div><img src="rt-icon.png"> <?= $row['rt_score'] ?></div><?php endif; ?>
        <?php if (!empty($row['metacritic_score'])): ?><div><img src="meta-icon.png"> <?= $row['metacritic_score'] ?></div><?php endif; ?>
      </div>

      <?php if (!empty($row['actors'])): ?>
      <div class="section-box">
        <strong>👥 שחקנים:</strong><br>
        <?php foreach (explode(',', $row['actors']) as $actor): ?>
          <span class="tag"><?= htmlspecialchars(trim($actor)) ?></span>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if (!empty($row['genre'])): ?>
      <div class="section-box">
        <strong>🎭 ז'אנר:</strong><br>
        <?php foreach (explode(',', $row['genre']) as $genre): ?>
          <span class="tag"><?= htmlspecialchars(trim($genre)) ?></span>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php
      $res_user = $conn->query("SELECT id, genre FROM user_tags WHERE poster_id = $id");
      if ($res_user->num_rows > 0): ?>
      <div class="section-box">
        <strong>🏷 תגיות משתמש:</strong><br>
        <?php while ($g = $res_user->fetch_assoc()): ?>
          <span class="tag"><?= htmlspecialchars($g['genre']) ?></span>
        <?php endwhile; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <?php
  $sim = $conn->query("SELECT p.id, p.title_en, p.image_url FROM poster_similar s JOIN posters p ON s.similar_id = p.id WHERE s.poster_id = $id");
  if ($sim->num_rows > 0): ?>
  <div class="section-box">
    <strong>🎬 סרטים דומים:</strong>
    <div style="display:flex; flex-wrap:wrap; gap:10px;">
      <?php while ($s = $sim->fetch_assoc()): ?>
        <a href="poster.php?id=<?= $s['id'] ?>" style="width:100px; text-align:center;">
          <img src="<?= htmlspecialchars($s['image_url']) ?>" style="width:100%; border-radius:4px;">
          <div style="font-size:12px; margin-top:5px;"> <?= htmlspecialchars($s['title_en']) ?> </div>
        </a>
      <?php endwhile; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="section-box">
    <strong>📝 תקציר:</strong><br>
    <?= nl2br(htmlspecialchars($row['plot'])) ?>
  </div>
</div>
</body>
</html>
<?php $conn->close(); ?>
