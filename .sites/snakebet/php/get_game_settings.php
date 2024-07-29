<?php
include 'db.php';

$query = $conn->prepare("SELECT * FROM game_settings WHERE id = 1");
$query->execute();
$settings = $query->get_result()->fetch_assoc();

echo json_encode($settings);
?>
