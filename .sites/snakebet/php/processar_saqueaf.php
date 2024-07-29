<?php
include 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $withdrawal_amount = $_POST['withdrawal_amount'];
    $pix_key = $_POST['pix_key'];

    // Inserir a solicitação de saque
    $query = $conn->prepare("INSERT INTO commission_withdrawals (user_id, amount, pix_key, status) VALUES (?, ?, ?, 'pending')");
    $query->bind_param("ids", $user_id, $withdrawal_amount, $pix_key);
    if ($query->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $query->error]);
    }
}
?>
