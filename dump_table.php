<?php
$conn = new mysqli('localhost', 'root', '123456', 'media');
if ($conn->connect_error) die("Connection failed");

$result = $conn->query("SHOW CREATE TABLE posters");
$row = $result->fetch_assoc();

echo "<pre>" . htmlspecialchars($row['Create Table']) . "</pre>";
?>
