<?php
$servername = "localhost";
$username = "cobrasnake";
$password = "6NA8nCRaRYRdePaB";
$dbname = "cobrasnake";

try {
    $db = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Definir o modo de erro do PDO para exceção
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conexão bem-sucedida";
} catch (PDOException $e) {
    echo "Erro de conexão: " . $e->getMessage();
    error_log("Erro ao conectar ao banco de dados: " . $e->getMessage());
    die("Erro ao conectar ao banco de dados");
}
?>
