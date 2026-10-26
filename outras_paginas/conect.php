<?php
$server_name = "localhost";
$username = "root";
$password = "";
$db_name = "Stardew store";

$conn = new mysqli($server_name, $username, $password, $db_name);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Define o conjunto de caracteres para evitar problemas de acentuação
$conn->set_charset("utf8mb4");
?>