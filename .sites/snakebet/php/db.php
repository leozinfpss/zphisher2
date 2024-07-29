<?php
$servername = "localhost:3306";
$username = "cobrasnake";
$password = "6NA8nCRaRYRdePaB";
$dbname = "cobrasnake";

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
?>
