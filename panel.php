<?php include 'header.php'; ?>

<?php
$conn = new mysqli('localhost', 'root', '123456', 'media');
if ($conn->connect_error) die("❌ חיבור למסד נכשל: " . $conn->connect_error);

// פונקציה בטוחה לספירה
function safeCount($conn, $table) {
  $res = $conn->query("SELECT COUNT(*) as c FROM $table");
  return ($res && $res->num_rows > 0) ? $res->fetch_assoc()['c'] : 0;
}

// סטטיסטיקות
$stats = [
  'posters'     => safeCount($conn, 'posters'),
  'collections' => safeCount($conn, 'collections'),
  'contacts'    => safeCount($conn, 'contact_requests'),
  'votes'       => safeCount($conn, 'poster_votes')
];

// פעילות אחרונה — עטוף כל אחת בבדיקה
$latest_contacts = $conn->query("SELECT * FROM contact_requests ORDER BY created_at DESC LIMIT 5");
$latest_votes = $conn->query("
  SELECT pv.*, p.title_en 
  FROM poster_votes pv 
  JOIN posters p ON p.id = pv.poster_id 
  ORDER BY pv.created_at DESC LIMIT 5
");
$latest_posters = $conn->query("SELECT * FROM posters ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>מרכז ניהול</title>
  <style>
    body { font-family: Arial; background:#f4f4f4; padding:40px; direction:rtl; }
    .box-grid { display:flex; flex-wrap:wrap; gap:20px; margin-bottom:40px; }
    .stat-box, .nav-box {
      background:#fff; padding:20px; border-radius:8px;
      box-shadow:0 0 6px rgba(0,0,0,0.1); flex:1; min-width:200px;
    }
    h2 { margin-bottom:20px; }
    .nav-box a {
      display:block; margin-bottom:10px; padding:10px;
      background:#007bff; color:#fff; text-decoration:none; border-radius:6px;
    }
    .nav-box a:hover { background:#0056b3; }
    .recent-box { margin-bottom:30px; }
    .entry {
      background:#fff; padding:10px; border-radius:6px;
      margin-bottom:10px; box-shadow:0 0 4px rgba(0,0,0,0.05);
    }
    .entry small { color:#888; font-size:12px; display:block; margin-top:6px; }
  </style>
</head>
<body>

<h1>📋 מרכז ניהול מערכת</h1>

<div class="box-grid">
  <div class="stat-box">
    <h2>📊 סטטיסטיקות</h2>
    <p>🎬 פוסטרים: <?= $stats['posters'] ?></p>
    <p>📦 אוספים: <?= $stats['collections'] ?></p>
    <p>📩 פניות צור קשר: <?= $stats['contacts'] ?></p>
    <p>❤️/💔 הצבעות: <?= $stats['votes'] ?></p>
  </div>

  <div class="nav-box">
    <h2>🧭 ניווט מהיר</h2>
    <a href="manage_posters.php">ניהול פוסטרים</a>
    <a href="manage_collections.php">ניהול אוספים</a>
    <a href="manage_contacts.php">ניהול פניות</a>
    <a href="manage_likes.php">ניהול הצבעות</a>
    <a href="likes_stats.php">סטטיסטיקת אהדה</a>
    <a href="collections.php" target="_blank">צפייה באוספים פומביים</a>
  </div>
</div>

<div class="recent-box">
  <h2>🕓 פעילות אחרונה</h2>

  <h3>📩 פניות אחרונות</h3>
  <?php if ($latest_contacts): while ($row = $latest_contacts->fetch_assoc()): ?>
    <div class="entry">
      <strong><?= htmlspecialchars($row['subject']) ?></strong>
      <small><?= htmlspecialchars($row['created_at']) ?> | <?= htmlspecialchars($row['email']) ?></small>
    </div>
  <?php endwhile; else: ?>
    <p>אין פניות זמינות.</p>
  <?php endif; ?>

  <h3>🗳️ הצבעות אחרונות</h3>
  <?php if ($latest_votes): while ($row = $latest_votes->fetch_assoc()): ?>
    <div class="entry">
      <strong><?= $row['vote_type'] === 'like' ? '❤️ אהבתי' : '💔 לא אהבתי' ?> על <?= htmlspecialchars($row['title_en']) ?></strong>
      <small><?= htmlspecialchars($row['created_at']) ?> | מזהה: <?= htmlspecialchars($row['visitor_token']) ?></small>
    </div>
  <?php endwhile; else: ?>
    <p>אין הצבעות זמינות.</p>
  <?php endif; ?>

  <h3>🆕 פוסטרים שנוספו</h3>
  <?php if ($latest_posters): while ($row = $latest_posters->fetch_assoc()): ?>
    <div class="entry">
      <strong><?= htmlspecialchars($row['title_en']) ?></strong>
      <small><?= htmlspecialchars($row['created_at']) ?> | ID: <?= $row['id'] ?></small>
    </div>
  <?php endwhile; else: ?>
    <p>אין פוסטרים חדשים.</p>
  <?php endif; ?>
</div>

</body>
</html>

<?php $conn->close(); ?>
<?php include 'footer.php'; ?>
