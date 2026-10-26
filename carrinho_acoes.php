<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$acao = $_GET['acao'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// 1. Adicionar ao Carrinho
if ($acao == 'adicionar' && $id > 0) {
    // Verifica se o produto existe e tem estoque
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = :id AND estoque > 0");
    $stmt->execute([':id' => $id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($produto) {
        // Vendedor não pode comprar o próprio produto
        if ($produto['usuario_id'] == $_SESSION['usuario_id']) {
            header("Location: index.php?mensagem=proprio_produto");
            exit();
        }

        if (isset($_SESSION['carrinho'][$id])) {
            if ($_SESSION['carrinho'][$id] < $produto['estoque']) {
                $_SESSION['carrinho'][$id]++;
            }
        } else {
            $_SESSION['carrinho'][$id] = 1;
        }
    }
    header("Location: carrinho.php");
    exit();
}

// 2. Remover item do Carrinho
if ($acao == 'remover' && $id > 0) {
    unset($_SESSION['carrinho'][$id]);
    header("Location: carrinho.php");
    exit();
}

// 3. Esvaziar Carrinho
if ($acao == 'limpar') {
    $_SESSION['carrinho'] = [];
    header("Location: carrinho.php");
    exit();
}

// 4. Finalizar Compra
if ($acao == 'finalizar') {
    if (empty($_SESSION['carrinho'])) {
        header("Location: carrinho.php");
        exit();
    }

    try {
        $pdo->beginTransaction();
        $comprador_id = $_SESSION['usuario_id'];

        foreach ($_SESSION['carrinho'] as $produto_id => $qtd) {
            // Busca o produto e verifica estoque
            $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = :id FOR UPDATE");
            $stmt->execute([':id' => $produto_id]);
            $prod = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($prod && $prod['estoque'] >= $qtd) {
                $valor_total = $prod['preco'] * $qtd;

                // Registra o pedido
                $stmt_ped = $pdo->prepare("INSERT INTO pedidos (usuario_id, produto_id, quantidade, valor_total, status) VALUES (:u, :p, :q, :v, 'Concluído')");
                $stmt_ped->execute([
                    ':u' => $comprador_id,
                    ':p' => $produto_id,
                    ':q' => $qtd,
                    ':v' => $valor_total
                ]);

                // Atualiza o estoque
                $stmt_est = $pdo->prepare("UPDATE produtos SET estoque = estoque - :q WHERE id = :id");
                $stmt_est->execute([':q' => $qtd, ':id' => $produto_id]);
            }
        }

        $pdo->commit();
        $_SESSION['carrinho'] = [];
        header("Location: meus_pedidos.php?compra=sucesso");
        exit();

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header("Location: carrinho.php?erro=falha");
        exit();
    }
}

header("Location: index.php");
exit();
?>
