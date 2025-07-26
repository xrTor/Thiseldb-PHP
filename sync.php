<?php include 'header.php'; 
require_once 'server.php';

$conn->set_charset("utf8");

// שליפת כל הפוסטרים עם image_url תקף ו imdb_id
$result = $conn->query("SELECT id, image_url, imdb_id FROM posters WHERE image_url LIKE 'http%' AND imdb_id IS NOT NULL");

echo "<h3>📥 סנכרון תמונות מהאינטרנט</h3>";
echo "<ul>";

while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $url = $row['image_url'];
    $imdb = $row['imdb_id'];

    $extension = pathinfo($url, PATHINFO_EXTENSION);
    if (!$extension || strlen($extension) > 5) $extension = 'jpg'; // סיומת ברירת מחדל

    $localPath = "uploads/" . $imdb . "." . strtolower($extension);

    $image = @file_get_contents($url);
    if ($image && file_put_contents($localPath, $image)) {
        // עדכון במסד
        $stmt = $conn->prepare("UPDATE posters SET image_url = ? WHERE id = ?");
        $stmt->bind_param("si", $localPath, $id);
        $stmt->execute();
        $stmt->close();

        echo "<li>✅ [{$id}] נשמר ל־{$localPath}</li>";
    } else {
        echo "<li>❌ [{$id}] שגיאה בהורדה מ־{$url}</li>";
    }
}
echo "</ul>";
?>

<?php include 'footer.php'; ?>