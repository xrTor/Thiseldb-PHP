<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
  <meta charset="UTF-8">
  <title>🔍 טופס סינון</title>
  <style>
    body {
      font-family: Arial;
      background: #f0f0f0;
      padding: 40px;
      text-align: center;
    }
    .flag-options label {
      display: inline-block;
      margin: 6px;
      padding: 6px 10px;
      background: #eaeaea;
      border-radius: 20px;
      cursor: pointer;
      transition: 0.2s;
    }
    .flag-options label:hover {
      background: #d0d0d0;
    }
  </style>
</head>
<body>

<h2>🔍 טופס סינון פוסטרים</h2>

<form method="get" action="home.php">
  <label>
    <input type="checkbox" id="is_foreign_language" name="is_foreign_language" value="1"
      <?= isset($_GET['is_foreign_language']) ? 'checked' : '' ?>>
    🌍 שפה זרה
  </label>

  <div id="languageMenu" style="display:none; margin-top:10px;">
    <div class="flag-options">
      <label><input type="checkbox" name="languages[]" value="en"> 🇺🇸 אנגלית</label>
      <label><input type="checkbox" name="languages[]" value="fr"> 🇫🇷 צרפתית</label>
      <label><input type="checkbox" name="languages[]" value="ja"> 🇯🇵 יפנית</label>
      <label><input type="checkbox" name="languages[]" value="he"> 🇮🇱 עברית</label>
      <label><input type="checkbox" name="languages[]" value="es"> 🇪🇸 ספרדית</label>
    </div>
  </div>

  <br><br>
  <button type="submit">📥 סנן</button>
</form>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const checkbox = document.getElementById('is_foreign_language');
  const menu = document.getElementById('languageMenu');

  if (!checkbox || !menu) return;

  function toggleFlags() {
    menu.style.display = checkbox.checked ? 'block' : 'none';
  }

  toggleFlags(); // בעת טעינת העמוד
  checkbox.addEventListener('change', toggleFlags); // בעת שינוי
});
</script>

</body>
</html>
