<style>
.languages-table {
  font-family: calibri;
  border-collapse: collapse;
  margin: 10px auto;
  direction: ltr; /* מיושר לשמאל */
}
.language-td {
  padding: 6px;
  text-align: left; /* יישור לשמאל */
  vertical-align: middle;
}
.language-cell {
  display: flex;
  align-items: center;
  justify-content: flex-start; /* אלמנטים מיושרים לשמאל */
  gap: 6px;
  font-size: 13px;
  cursor: pointer;
}
.language-cell input[type="checkbox"] {
  transform: scale(1.2);
}
.language-cell img {
  height: 16px;
}
.language-cell span {
  flex-grow: 1;
}
</style>

<?php
include 'languages.php';

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
          <label class='language-cell'>
            <input type='checkbox' name='languages[]' value='{$lang['code']}' $checked>
            <img src='{$lang['flag']}' alt='{$lang['label']}' title='{$lang['label']}'>
            <span>{$lang['label']}</span>
          </label>
        </td>";

  $i++;
  if ($i % $columns === 0 && $i < $max_count) echo '</tr><tr>';
}
echo '</tr></table>';
?>
