<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $produto_id = (int)$_GET['id'];
    $comprador_id = $_SESSION['usuario_id'];

    try {
        // Busca informações do produto
        $sql_busca = "SELECT * FROM produtos WHERE id = :id AND estoque > 0";
        $stmt_busca = $pdo->prepare($sql_busca);
        $stmt_busca->execute([':id' => $produto_id]);
        $produto = $stmt_busca->fetch(PDO::FETCH_ASSOC);

        if ($produto) {
            // Garante que o vendedor não compre o próprio produto
            if ($produto['usuario_id'] == $comprador_id) {
                header("Location: index.php?mensagem=proprio_produto");
                exit();
            }

            // Inicia uma transação
            $pdo->beginTransaction();

            // 1. Registra o pedido na tabela pedidos
            $sql_pedido = "INSERT INTO pedidos (usuario_id, produto_id, quantidade, valor_total, status) 
                           VALUES (:usuario_id, :produto_id, 1, :valor_total, 'Concluído')";
            $stmt_pedido = $pdo->prepare($sql_pedido);
            $stmt_pedido->execute([
                ':usuario_id' => $comprador_id,
                ':produto_id' => $produto_id,
                ':valor_total' => $produto['preco']
            ]);

            // 2. Reduz o estoque do produto em 1
            $sql_estoque = "UPDATE produtos SET estoque = estoque - 1 WHERE id = :id";
            $stmt_estoque = $pdo->prepare($sql_estoque);
            $stmt_estoque->execute([':id' => $produto_id]);

            $pdo->commit();
            header("Location: index.php?compra=sucesso");
            exit();
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header("Location: index.php?erro=falha_compra");
        exit();
    }
}

header("Location: index.php");
exit();
?>