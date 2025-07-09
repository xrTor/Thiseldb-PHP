<!-- 🌐 שפות עם דגלים -->
  <div style="margin:10px 0;" class="ltr">
    <strong>שפות זרות:</strong><br>
    <?php
$languages = [
   ['code' => 'spanish',
  'label' => 'spanish',
  'flag' => 'flags/spanish.gif'],

  ['code' => 'french',
  'label' => 'french',
  'flag' => 'flags/french.gif'],

  ['code' => 'arabic',
  'label' => 'arabic',
  'flag' => 'flags/arabic.gif'],

  ['code' => 'portuguese',
  'label' => 'portuguese',
  'flag' => 'flags/portuguese.gif'],

  ['code' => 'brazilian-portuguese',
  'label' => 'brazilian portuguese',
  'flag' => 'flags/brazilian-portuguese.gif'],

  ['code' => 'bulgarian',
  'label' => 'bulgarian',
  'flag' => 'flags/bulgarian.gif'],

  ['code' => 'chinese',
  'label' => 'chinese',
  'flag' => 'flags/chinese.gif'],

  ['code' => 'croatian',
  'label' => 'croatian',
  'flag' => 'flags/croatian.gif'],

  ['code' => 'czech',
  'label' => 'czech',
  'flag' => 'flags/czech.gif'],

  ['code' => 'danish',
  'label' => 'danish',
  'flag' => 'flags/danish.gif'],

  ['code' => 'dutch',
  'label' => 'dutch',
  'flag' => 'flags/dutch.gif'],

  ['code' => 'estonian',
  'label' => 'estonian',
  'flag' => 'flags/estonian.gif'],

  ['code' => 'finnish',
  'label' => 'finnish',
  'flag' => 'flags/finnish.gif'],

  ['code' => 'german',
  'label' => 'german',
  'flag' => 'flags/german.gif'],

  ['code' => 'greek',
  'label' => 'greek',
  'flag' => 'flags/greek.gif'],

  ['code' => 'hindi',
  'label' => 'hindi',
  'flag' => 'flags/hindi.gif'],

  ['code' => 'hungarian',
  'label' => 'hungarian',
  'flag' => 'flags/hungarian.gif'],

  ['code' => 'icelandic',
  'label' => 'icelandic',
  'flag' => 'flags/icelandic.gif'],

  ['code' => 'indonesian',
  'label' => 'indonesian',
  'flag' => 'flags/indonesian.gif'],

  ['code' => 'italian',
  'label' => 'italian',
  'flag' => 'flags/italian.gif'],

  ['code' => 'japanese',
  'label' => 'japanese',
  'flag' => 'flags/japanese.gif'],

  ['code' => 'korean',
  'label' => 'korean',
  'flag' => 'flags/korean.gif'],

  ['code' => 'latvian',
  'label' => 'latvian',
  'flag' => 'flags/latvian.gif'],

  ['code' => 'lithuanian',
  'label' => 'lithuanian',
  'flag' => 'flags/lithuanian.gif'],

  ['code' => 'malay',
  'label' => 'malay',
  'flag' => 'flags/malay.gif'],

  ['code' => 'norwegian',
  'label' => 'norwegian',
  'flag' => 'flags/norwegian.gif'],

  ['code' => 'persian',
  'label' => 'persian',
  'flag' => 'flags/persian.gif'],

  ['code' => 'polish',
  'label' => 'polish',
  'flag' => 'flags/polish.gif'],

  ['code' => 'romanian',
  'label' => 'romanian',
  'flag' => 'flags/romanian.gif'],

  ['code' => 'russian',
  'label' => 'russian',
  'flag' => 'flags/russian.gif'],

  ['code' => 'serbian',
  'label' => 'serbian',
  'flag' => 'flags/serbian.gif'],

  ['code' => 'slovak',
  'label' => 'slovak',
  'flag' => 'flags/slovak.gif'],

  ['code' => 'slovenian',
  'label' => 'slovenian',
  'flag' => 'flags/slovenian.gif'],

  ['code' => 'swedish',
  'label' => 'swedish',
  'flag' => 'flags/swedish.gif'],

  ['code' => 'thai',
  'label' => 'thai',
  'flag' => 'flags/thai.gif'],

  ['code' => 'turkish',
  'label' => 'turkish',
  'flag' => 'flags/turkish.gif'],

  ['code' => 'ukrainian',
  'label' => 'ukrainian',
  'flag' => 'flags/ukrainian.gif'],

  ['code' => 'vietnamese',
  'label' => 'vietnamese',
  'flag' => 'flags/vietnamese.gif'],

  ['code' => 'welsh',
  'label' => 'welsh',
  'flag' => 'flags/welsh.gif'],

];

 foreach ($languages as $lang) {
  $checked = isset($_GET['languages']) && in_array($lang['code'], $_GET['languages']) ? 'checked' : '';
  echo "<label style='display:inline-block; margin:6px;'>
          <input type='checkbox' name='languages[]' value='{$lang['code']}' $checked>
          <img src='{$lang['flag']}' alt='{$lang['label']}' title='{$lang['label']}' style='height:16px; vertical-align:middle;'> 
        </label>";
}
/*
{$lang['label']}
 */
    ?>
  </div>