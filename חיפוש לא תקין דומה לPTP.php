<form method="get" action="bar.php" dir="rtl" style="font-family:Arial; max-width:800px; margin:0 auto; background:#f9f9f9; padding:20px; border:1px solid #ccc; border-radius:10px;">
  <h2 style="margin-top:0;">🔍 חיפוש וסינון פוסטרים</h2>

  <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:15px;">
    <input type="text" name="search" placeholder="חיפוש כותרת" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    <input type="text" name="year" placeholder="שנה (כגון: 2010,2012)" value="<?= htmlspecialchars($_GET['year'] ?? '') ?>">
    
    <input type="text" name="min_rating" placeholder="דירוג מינימלי" value="<?= htmlspecialchars($_GET['min_rating'] ?? '') ?>">
    <input type="text" name="imdb_id" placeholder="מספר IMDb" value="<?= htmlspecialchars($_GET['imdb_id'] ?? '') ?>">
    
    <input type="text" name="type" placeholder="סוג (film, series)" value="<?= htmlspecialchars($_GET['type'] ?? '') ?>">
    <input type="text" name="genre" placeholder="ז'אנר (מופרד בפסיקים)" value="<?= htmlspecialchars($_GET['genre'] ?? '') ?>">
    
    <input type="text" name="actor" placeholder="שחקן (מופרד בפסיקים)" value="<?= htmlspecialchars($_GET['actor'] ?? '') ?>">
  </div>

  <div style="margin-bottom:10px;">
    <strong>שפות כתוביות:</strong><br>
    <?php
    $all_langs = ['English', 'Spanish', 'French', 'Hebrew', 'German'];
    $langs_selected = $_GET['languages'] ?? [];
    if (!is_array($langs_selected)) $langs_selected = [$langs_selected];
    foreach ($all_langs as $lang) {
      $checked = in_array($lang, $langs_selected) ? 'checked' : '';
      echo "<label style='margin-left:10px;'><input type='checkbox' name='languages[]' value='$lang' $checked> $lang</label>";
    }
    ?>
  </div>

  <div style="margin-bottom:10px;">
    <strong>אפשרויות נוספות:</strong><br>
    <label><input type="checkbox" name="is_dubbed" value="1" <?= !empty($_GET['is_dubbed']) ? 'checked' : '' ?>> מדובב</label>
    <label><input type="checkbox" name="is_netflix_exclusive" value="1" <?= !empty($_GET['is_netflix_exclusive']) ? 'checked' : '' ?>> Netflix בלעדי</label>
    <label><input type="checkbox" name="is_foreign_language" value="1" <?= !empty($_GET['is_foreign_language']) ? 'checked' : '' ?>> שפה זרה</label>
    <label><input type="checkbox" name="missing_translation" value="1" <?= !empty($_GET['missing_translation']) ? 'checked' : '' ?>> חסר תרגום</label>
  </div>

  <div style="margin-bottom:10px;">
    <label>מיון:</label>
    <select name="sort">
      <option value="">ללא</option>
      <option value="year_asc" <?= (($_GET['sort'] ?? '') === 'year_asc') ? 'selected' : '' ?>>שנה עולה</option>
      <option value="year_desc" <?= (($_GET['sort'] ?? '') === 'year_desc') ? 'selected' : '' ?>>שנה יורדת</option>
      <option value="rating_desc" <?= (($_GET['sort'] ?? '') === 'rating_desc') ? 'selected' : '' ?>>דירוג יורד</option>
    </select>

    <label style="margin-right:20px;">מספר תוצאות לעמוד:</label>
    <select name="limit">
      <?php
      $limits = [5, 10, 20, 50, 100, 250];
      $current_limit = $_GET['limit'] ?? 20;
      foreach ($limits as $l) {
        $selected = ($l == $current_limit) ? 'selected' : '';
        echo "<option value=\"$l\" $selected>$l</option>";
      }
      ?>
    </select>
  </div>

  <div style="margin-bottom:15px;">
    <label><strong>מצב חיפוש:</strong></label><br>
    <label><input type="radio" name="search_mode" value="or" <?= (($_GET['search_mode'] ?? '') !== 'and') ? 'checked' : '' ?>> OR (או)</label>
    <label><input type="radio" name="search_mode" value="and" <?= (($_GET['search_mode'] ?? '') === 'and') ? 'checked' : '' ?>> AND (וגם)</label>
  </div>

  <button type="submit" style="padding:10px 20px; background:#4CAF50; color:white; border:none; border-radius:5px;">🔍 חפש וסנן</button>
</form>
