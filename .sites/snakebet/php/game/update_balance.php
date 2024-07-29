<?php
// Conexão com o banco de dados
include '../php/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$userId = $input['userId'];
$balance = $input['balance'];

// Atualizar o saldo do usuário no banco de dados
$query = $db->prepare("UPDATE users SET balance = :balance WHERE id = :userId");
$query->bindParam(':balance', $balance);
$query->bindParam(':userId', $userId);
$query->execute();

if ($query->rowCount() > 0) {
    echo json_encode(['success' => true, 'message' => 'Saldo atualizado com sucesso']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar saldo']);
}
?>
