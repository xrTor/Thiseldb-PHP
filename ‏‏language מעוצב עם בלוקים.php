<?php
include 'languages.php';
?>

<!DOCTYPE html>
<html lang="he">
<head>
  <meta charset="UTF-8">
  <title>סינון לפי שפה</title>
  <style>
    body {
      font-family: Calibri, sans-serif;
      background: #f2f2f2;
      margin: 20px;
      text-align: center;
      direction: rtl;
    }
    .language-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
      gap: 12px;
      max-width: 960px;
      margin: 0 auto;
    }
    .language-item {
      background: #fff;
      border: 1px solid #ccc;
      border-radius: 6px;
      padding: 8px;
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
      transition: background 0.3s ease;
    }
    .language-item:hover {
      background: #e6f0ff;
    }
    .language-item img {
      height: 16px;
    }
    .language-item a {
      text-decoration: none;
      color: #333;
      flex-grow: 1;
      text-align: left;
    }
  </style>
</head>
<body>
  <h2>🌐 סינון פוסטרים לפי שפה</h2>
  <div class="language-grid">
    <?php foreach ($languages as $lang): ?>
      <div class="language-item">
        <img src="<?= $lang['flag'] ?>" alt="<?= $lang['label'] ?>">
        <a href="home.php?languages[]=<?= urlencode($lang['code']) ?>">
          <?= htmlspecialchars($lang['label']) ?>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
</body>
</html>
