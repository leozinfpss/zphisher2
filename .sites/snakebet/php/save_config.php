<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ci = $_POST['ci'];
    $cs = $_POST['cs'];
    $suitpay_url = $_POST['suitpay_url'];
    $callback_url = $_POST['callback_url'];

    $configs = [
        'ci' => $ci,
        'cs' => $cs,
        'suitpay_url' => $suitpay_url,
        'callback_url' => $callback_url
    ];

    foreach ($configs as $key => $value) {
        $query = $conn->prepare("INSERT INTO configurations (`key`, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)");
        $query->bind_param("ss", $key, $value);
        $query->execute();
    }

    header("Location: ../views/suitpay.php");
    exit();
}
?>
