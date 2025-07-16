<?php
 require_once 'server.php';
 
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="posters.csv"');

$output = fopen("php://output", "w");
fputcsv($output, ['ID', 'Title', 'Year', 'IMDb Rating', 'Type']);

$res = $conn->query("SELECT id, title_en, year, imdb_rating, type FROM posters");
while ($row = $res->fetch_assoc()) {
  fputcsv($output, [$row['id'], $row['title_en'], $row['year'], $row['imdb_rating'], $row['type']]);
}

fclose($output);
$conn->close();
?>
