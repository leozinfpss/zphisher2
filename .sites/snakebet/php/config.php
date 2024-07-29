<?php
function get_config($key) {
    global $conn;
    $query = $conn->prepare("SELECT value FROM configurations WHERE `key` = ?");
    $query->bind_param("s", $key);
    $query->execute();
    $result = $query->get_result();
    $data = $result->fetch_assoc();
    return $data['value'] ?? null;
}
?>
