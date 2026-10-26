<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$carrinho_itens = [];
$total_geral = 0;

if (!empty($_SESSION['carrinho'])) {
    $ids = implode(',', array_map('intval', array_keys($_SESSION['carrinho'])));
    $sql = "SELECT p.*, u.nome AS vendedor_nome FROM produtos p JOIN usuarios u ON p.usuario_id = u.id WHERE p.id IN ($ids)";
    $stmt = $pdo->query($sql);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($produtos as $p) {
        $qtd = $_SESSION['carrinho'][$p['id']];
        $subtotal = $p['preco'] * $qtd;
        $total_geral += $subtotal;

        $carrinho_itens[] = [
            'id' => $p['id'],
            'titulo' => $p['titulo'],
            'preco' => $p['preco'],
            'imagem' => $p['imagem'],
            'vendedor' => $p['vendedor_nome'],
            'quantidade' => $qtd,
            'subtotal' => $subtotal
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Carrinho de Garimpo - Midori’s Music Shop</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <header>
        <h1>Midori’s Music Shop 🎧⚡</h1>
        <p><a href="index.php">← Voltar para a Vitrine</a></p>
    </header>

    <main>
        <h2>Seu Carrinho de Garimpo 🛒</h2>

        <?php if (empty($carrinho_itens)): ?>
            <p style="color: #a3ffce; margin-top: 15px;">Seu carrinho está vazio. Explore a loja e adicione relíquias de áudio!</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 15px; margin-top: 20px;">
                <?php foreach ($carrinho_itens as $item): ?>
                    <div class="card-produto" style="display: flex; gap: 15px; align-items: center; justify-content: space-between;">
                        <div style="display: flex; gap: 15px; align-items: center;">
                            <?php if (!empty($item['imagem'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($item['imagem']); ?>" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #00ff66;">
                            <?php endif; ?>
                            <div>
                                <h3><?php echo htmlspecialchars($item['titulo']); ?></h3>
                                <p><strong>Vendedor:</strong> <?php echo htmlspecialchars($item['vendedor']); ?></p>
                                <p><strong>Qtd:</strong> <?php echo $item['quantidade']; ?>x | <strong>Subtotal:</strong> R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></p>
                            </div>
                        </div>
                        <a href="carrinho_acoes.php?acao=remover&id=<?php echo $item['id']; ?>" style="color: #ff4d4d; border: 1px solid #ff4d4d; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-weight: bold;">Remover</a>
                    </div>
                <?php endforeach; ?>

                <div style="margin-top: 20px; background: rgba(0, 255, 102, 0.08); padding: 20px; border-radius: 8px; border: 1px solid #00ff66; text-align: right;">
                    <h3 style="font-size: 22px; color: #00ff66;">Total: R$ <?php echo number_format($total_geral, 2, ',', '.'); ?></h3>
                    <br>
                    <a href="carrinho_acoes.php?acao=limpar" style="color: #ff4d4d; margin-right: 15px; text-decoration: underline;">Esvaziar Carrinho</a>
                    <a href="carrinho_acoes.php?acao=finalizar" onclick="return confirm('Confirmar o fechamento do pedido no garimpo?');" style="background: #00ff66; color: #000; padding: 12px 24px; border-radius: 6px; font-weight: bold; text-decoration: none; text-transform: uppercase;">⚡ Finalizar Pedido</a>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>