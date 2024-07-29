<?php
include 'db.php';

if (!isset($_POST['transaction_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'transaction_id missing']);
    exit();
}

$transaction_id = $_POST['transaction_id'];

$query = $conn->prepare("SELECT status FROM deposits WHERE transaction_id = ?");
$query->bind_param("s", $transaction_id);
$query->execute();
$result = $query->get_result();
$data = $result->fetch_assoc();

if ($data) {
    echo json_encode(['status' => $data['status']]);
} else {
    echo json_encode(['status' => 'not_found']);
}
?>
