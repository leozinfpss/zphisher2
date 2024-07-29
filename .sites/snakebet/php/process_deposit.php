<?php
session_start();
include 'db.php';
include 'config.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['session_token'])) {
    header("Location: ../login.php");
    exit();
}

// Obter o ID do usuário a partir do token de sessão
$query = $conn->prepare("SELECT user_id FROM sessions WHERE session_token = ?");
$query->bind_param("s", $_SESSION['session_token']);
$query->execute();
$result = $query->get_result();
$session_data = $result->fetch_assoc();

if ($session_data) {
    $user_id = $session_data['user_id'];

    // Capturar valores do formulário
    $nome = $_POST['name'];
    $cpf = $_POST['cpf'];
    $valor = $_POST['valor'];
    $due_date = date('Y-m-d', strtotime('+1 day')); // Data de vencimento para um dia depois

    // Verificar se os valores do banco de dados estão sendo recuperados corretamente
    $ci = get_config('ci');
    $cs = get_config('cs');
    $suitpay_url = get_config('suitpay_url');
    $callback_url = get_config('callback_url');

    if (!$ci || !$cs || !$suitpay_url || !$callback_url) {
        echo json_encode(['status' => 'error', 'message' => 'Configuration values are missing']);
        exit();
    }

    $data = [
        'requestNumber' => uniqid(),
        'dueDate' => $due_date,
        'amount' => (float) $valor,
        'callbackUrl' => $callback_url, // URL do webhook do banco de dados
        'client' => [
            'name' => $nome,
            'document' => $cpf,
            'email' => 'email@example.com' // Supondo que o email esteja no banco ou sessão
        ]
    ];

    $headers = [
        'ci: ' . $ci,
        'cs: ' . $cs,
        'Content-Type: application/json'
    ];

    $ch = curl_init($suitpay_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $curl_error = curl_error($ch); // Capturar erros do cURL
    curl_close($ch);

    $response_data = json_decode($response, true);

    if ($response === false) {
        echo json_encode(['status' => 'error', 'message' => 'cURL Error: ' . $curl_error]);
    } elseif (isset($response_data['idTransaction'])) {
        $transaction_id = $response_data['idTransaction'];
        $payment_code_base64 = $response_data['paymentCodeBase64'];
        $payment_code = $response_data['paymentCode'];

        // Inserir depósito no banco de dados
        $query = $conn->prepare("INSERT INTO deposits (user_id, value, status, transaction_id) VALUES (?, ?, 'pending', ?)");
        $query->bind_param("ids", $user_id, $valor, $transaction_id);
        $query->execute();

        // Retornar o QR Code para exibição e o transaction_id para verificação
        echo json_encode(['status' => 'success', 'qr_code' => $payment_code_base64, 'paymentCode' => $payment_code, 'transaction_id' => $transaction_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to generate QR Code', 'response' => $response_data]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid session']);
}
?>
