<?php
include 'db.php';

// Receber dados do webhook
$input = file_get_contents('php://input');
$data = json_decode($input, true);

error_log("Webhook received: " . json_encode($data)); // Log para debug

if (isset($data['idTransaction']) && isset($data['statusTransaction'])) {
    $transaction_id = $data['idTransaction'];
    $status = $data['statusTransaction'] == 'PAID_OUT' ? 'confirmed' : 'failed';

    // Atualizar o status do depósito no banco de dados
    $query = $conn->prepare("UPDATE deposits SET status = ?, updated_at = NOW() WHERE transaction_id = ?");
    $query->bind_param("ss", $status, $transaction_id);
    $query->execute();

    if ($query->affected_rows > 0 && $status == 'confirmed') {
        // Se a transação foi confirmada, atualizar o saldo do usuário
        $query = $conn->prepare("SELECT user_id, value FROM deposits WHERE transaction_id = ?");
        $query->bind_param("s", $transaction_id);
        $query->execute();
        $result = $query->get_result();
        $deposit = $result->fetch_assoc();

        if ($deposit) {
            $user_id = $deposit['user_id'];
            $value = $deposit['value'];

            $query = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $query->bind_param("di", $value, $user_id);
            $query->execute();

            if ($query->affected_rows > 0) {
                error_log("Balance updated for user $user_id by $value");
            } else {
                error_log("Failed to update balance for user $user_id");
            }
        } else {
            error_log("Deposit not found for transaction $transaction_id");
        }
    }

    error_log("Transaction $transaction_id updated to $status");
} else {
    error_log("Invalid data received");
}
?>
