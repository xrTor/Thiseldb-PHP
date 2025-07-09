<style>
.languages-table {
  font-family: calibri;
  border-collapse: collapse;
  margin: 10px auto;
  direction: ltr;
}

.language-td {
  padding: 6px;
  text-align: left;
  vertical-align: middle;
}

.language-cell {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 6px;
  font-size: 13px;
}

.language-cell input[type="checkbox"] {
  transform: scale(1.2);
  cursor: pointer;
  order: 0; /* תיבה בצד שמאל */
}

.language-cell img {
  height: 16px;
  order: 1; /* דגל אחרי התיבה */
}

.language-cell span {
  order: 2; /* שם השפה אחרי הדגל */
  flex-grow: 1;
}

</style>
<?php
include 'languages.php'; // כולל את המערך המלא של השפות

$columns = 5;
$rows_limit = 10;
$max_count = $columns * $rows_limit;
$i = 0;

echo '<table class="languages-table">';
echo '<tr><td colspan="5" style="text-align:left;"><strong>🌐 Source Languages:</strong></td></tr><tr>';

foreach ($languages as $lang) {
  if ($i >= $max_count) break;

  $checked = isset($_GET['languages']) && in_array($lang['code'], $_GET['languages']) ? 'checked' : '';
  echo "<td class='language-td'>
          <div class='language-cell'>
            <img src='{$lang['flag']}' alt='{$lang['label']}' title='{$lang['label']}'>
            <span>{$lang['label']}</span>
            <input type='checkbox' name='languages[]' value='{$lang['code']}' $checked>
          </div>
        </td>";

  $i++;
  if ($i % $columns === 0 && $i < $max_count) echo '</tr><tr>';
}
echo '</tr></table>';
