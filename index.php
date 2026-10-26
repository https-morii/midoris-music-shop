<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$mensagem_alerta = "";

if (isset($_GET['mensagem']) && $_GET['mensagem'] == 'proprio_produto') {
    $mensagem_alerta = "⚠️ Você não pode adicionar ao carrinho um produto anunciado por você mesmo!";
}

// Contador de itens no carrinho
$qtd_carrinho = isset($_SESSION['carrinho']) ? array_sum($_SESSION['carrinho']) : 0;

try {
    $sql = "SELECT p.*, c.nome AS categoria_nome, u.nome AS vendedor_nome 
            FROM produtos p
            JOIN categorias c ON p.categoria_id = c.id
            JOIN usuarios u ON p.usuario_id = u.id
            WHERE p.estoque > 0
            ORDER BY p.criado_em DESC";

    $stmt = $pdo->query($sql);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $produtos = [];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Midori’s Music Shop - Início</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <header>
        <h1>Midori’s Music Shop 🎧⚡</h1>
        <p>Bem-vindo(a), <strong><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'Visitante'); ?></strong>! [<?php echo strtoupper(htmlspecialchars($_SESSION['usuario_tipo'] ?? 'comum')); ?>]</p>
        <br>
        <nav>
            <a href="index.php">Início</a> | 
            <?php if (isset($_SESSION['usuario_tipo']) && ($_SESSION['usuario_tipo'] == 'vendedor' || $_SESSION['usuario_tipo'] == 'admin')): ?>
                <a href="cadastrar_produto.php">Anunciar Produto</a> | 
            <?php endif; ?>
            <a href="carrinho.php">🛒 Carrinho (<?php echo $qtd_carrinho; ?>)</a> | 
            <a href="meus_pedidos.php">Meus Pedidos</a> | 
            <a href="logout.php">Sair</a>
        </nav>
    </header>

    <main>
        <h2>Garimpo Musical & Brechó de Áudio</h2>

        <?php if (!empty($mensagem_alerta)): ?>
            <p style="color: #ff4d4d; background: rgba(255, 77, 77, 0.1); padding: 12px; border: 1px solid #ff4d4d; border-radius: 8px; margin-top: 15px; text-align: center;">
                <strong><?php echo $mensagem_alerta; ?></strong>
            </p>
        <?php endif; ?>

        <?php if (empty($produtos)): ?>
            <p style="margin-top: 15px; color: #a3ffce;">Nenhum produto cadastrado no momento. Seja o primeiro a anunciar!</p>
        <?php else: ?>
            <div class="vitrine">
                <?php foreach ($produtos as $produto): ?>
                    <div class="card-produto">
                        <?php if (!empty($produto['imagem'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($produto['imagem']); ?>" alt="<?php echo htmlspecialchars($produto['titulo']); ?>" style="width: 100%; height: 180px; object-fit: cover; border-radius: 8px; margin-bottom: 12px; border: 1px solid #00ff66;">
                        <?php else: ?>
                            <div style="width: 100%; height: 180px; background: rgba(0,255,102,0.05); border: 1px dashed #00ff66; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; color: #00ff66;">Sem foto</div>
                        <?php endif; ?>

                        <h3><?php echo htmlspecialchars($produto['titulo']); ?></h3>
                        <p><strong>Categoria:</strong> <?php echo htmlspecialchars($produto['categoria_nome']); ?></p>
                        <p class="preco">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
                        <p><strong>Estado:</strong> <?php echo htmlspecialchars($produto['estado_conservacao']); ?></p>
                        <p><strong>Estoque:</strong> <?php echo $produto['estoque']; ?> un.</p>
                        <p><strong>Vendedor:</strong> <?php echo htmlspecialchars($produto['vendedor_nome']); ?></p>
                        <p style="margin-top: 8px; font-size: 15px; color: #b3ffd9;">
                            <?php echo nl2br(htmlspecialchars($produto['descricao'])); ?>
                        </p>

                        <div style="margin-top: 15px;">
                            <?php if ($produto['usuario_id'] != $usuario_id): ?>
                                <a href="carrinho_acoes.php?acao=adicionar&id=<?php echo $produto['id']; ?>" style="display: block; text-align: center; background: #00ff66; color: #000; padding: 10px; border-radius: 6px; font-weight: bold; text-transform: uppercase;">🛒 Adicionar ao Carrinho</a>
                            <?php else: ?>
                                <p style="font-size: 13px; color: #00ff66; margin-bottom: 5px;">(Seu anúncio)</p>
                            <?php endif; ?>

                            <?php if ($produto['usuario_id'] == $usuario_id || $_SESSION['usuario_tipo'] == 'admin'): ?>
                                <a href="excluir_produto.php?id=<?php echo $produto['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir este anúncio do garimpo?');" style="display: block; text-align: center; background: rgba(255, 77, 77, 0.2); color: #ff4d4d; border: 1px solid #ff4d4d; padding: 8px; border-radius: 6px; font-weight: bold; margin-top: 8px; font-size: 14px;">🗑️ Excluir Anúncio</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>