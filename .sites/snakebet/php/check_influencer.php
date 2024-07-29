<?php
include 'db.php';

if (!isset($_GET['token'])) {
    echo json_encode(['error' => 'Token is required']);
    exit();
}

$token = $_GET['token'];

$query = $conn->prepare("SELECT user_id FROM sessions WHERE session_token = ?");
$query->bind_param("s", $token);
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['isInfluencer' => false]);
    exit();
}

$user = $result->fetch_assoc();
$user_id = $user['user_id'];

$query = $conn->prepare("SELECT is_influencer FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['isInfluencer' => false]);
    exit();
}

$user = $result->fetch_assoc();
$isInfluencer = $user['is_influencer'];

echo json_encode(['isInfluencer' => $isInfluencer]);
exit();
?>
