
<?php
$current = basename($_SERVER['PHP_SELF']);
echo "<!-- current page: $current -->";
?>


<div class="w3-bar w3-light-grey w3-padding w3-white" style="text-align:center; ">
  <?php
  $pages = [

    'index.php' => 'עמוד ראשי',
    'home.php' => 'חיפוש',
    //'movies.php' => '🎬 סרטים',
    //'series.php' => '📺 סדרות', 
    'random.php' => '🎲 סרט רנדומלי',
    'collections.php' => 'אוספים',
    'top.php' => '🏆 TOP 10',
     'stats.php' => '📈 סטטיסטיקה',
     'contact.php' => 'צור קשר',
     'about.php' => 'אודות',
  ];

    foreach ($pages as $file => $label) {
    $active = $current == $file ? 'active w3-black' : '';
    echo "<a href='$file' class='w3-button $active'>$label</a>";
  }/*w3-white*/
  ?>
</div>

<div class="w3-bar w3-light-grey w3-padding" style="text-align:center;">
  <?php
  $pages = [

    'add.php' => '➕ הוסף פוסטר חדש',
    'export.php' => '📤 ייצוא לCSV',
    'panel.php' => 'פאנל ניהול',
    
  ];

    foreach ($pages as $file => $label) {
    $active = $current == $file ? 'active w3-black' : '';
    echo "<a href='$file' class='w3-button $active'>$label</a>";
  }
  ?>
</div>