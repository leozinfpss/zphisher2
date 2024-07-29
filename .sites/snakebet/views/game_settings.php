<?php
session_start();
include 'db.php';

if (!isset($_SESSION['session_token'])) {
    header("Content-Type: application/json");
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$query = $conn->prepare("SELECT * FROM game_settings WHERE id = 1");
$query->execute();
$settings = $query->get_result()->fetch_assoc();

header("Content-Type: application/json");
echo json_encode($settings);
?>
