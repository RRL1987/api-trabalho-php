<?php
$host = "127.0.0.1";
$user = "root";
$password = "aluno";
$database = "banco_noite";
$port = "3306";
// Criando conexão
$conn = mysqli_connect($host, $user, $password, $database,$port);
if (!$conn) {
    die("Erro na conexão:" . mysqli_connect_error());
}

?>