<?php
$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "midoris_music_shop";

// Criando a conexão com o MySQL usando PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$banco;charset=utf8mb4", $usuario, $senha);
    // Configura o PDO para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Conexão realizada com sucesso!"; // Descomente para testar
} catch (PDOException $e) {
    echo "Erro na conexão: " . $e->getMessage();
}
?>