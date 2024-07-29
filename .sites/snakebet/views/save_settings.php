<?php
session_start();
include '../php/db.php';
include '../php/functions.php';

if (!isset($_SESSION['session_token']) || !isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$query = $conn->prepare("SELECT admin FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 0 || $result->fetch_assoc()['admin'] != 1) {
    header("Location: ../login.php");
    exit();
}

// Salvar configurações enviadas pelo formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'influencerFoodInitialCount' => $_POST['influencerFoodInitialCount'],
        'influencerEnemyInitialCount' => $_POST['influencerEnemyInitialCount'],
        'influencerFoodSpawnRate' => $_POST['influencerFoodSpawnRate'],
        'influencerEnemySpawnRate' => $_POST['influencerEnemySpawnRate'],
        'influencerFoodValueMultiplier' => $_POST['influencerFoodValueMultiplier'],
    ];

    foreach ($settings as $key => $value) {
        $query = $conn->prepare("UPDATE game_settings SET setting_value = ? WHERE setting_key = ?");
        $query->bind_param("ss", $value, $key);
        $query->execute();
    }

    header("Location: admin.php");
    exit();
}
?>
