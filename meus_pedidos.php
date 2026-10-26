<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Busca compras realizadas pelo usuário
$sql_compras = "SELECT ped.*, prod.titulo, prod.imagem, u.nome AS vendedor_nome 
                FROM pedidos ped
                JOIN produtos prod ON ped.produto_id = prod.id
                JOIN usuarios u ON prod.usuario_id = u.id
                WHERE ped.usuario_id = :usuario_id
                ORDER BY ped.criado_em DESC";
$stmt_c = $pdo->prepare($sql_compras);
$stmt_c->execute([':usuario_id' => $usuario_id]);
$compras = $stmt_c->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Meus Pedidos - Midori’s Music Shop</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <header>
        <h1>Midori’s Music Shop 🎧⚡</h1>
        <p><a href="index.php">← Voltar para a Página Inicial</a></p>
    </header>

    <main>
        <h2>Histórico de Garimpos & Compras 🛍️</h2>

        <?php if (empty($compras)): ?>
            <p style="color: #a3ffce; margin-top: 15px;">Você ainda não comprou nenhum item no garimpo.</p>
        <?php else: ?>
            <div class="vitrine">
                <?php foreach ($compras as $item): ?>
                    <div class="card-produto">
                        <?php if (!empty($item['imagem'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($item['imagem']); ?>" alt="<?php echo htmlspecialchars($item['titulo']); ?>" style="width: 100%; height: 160px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;">
                        <?php endif; ?>
                        <h3><?php echo htmlspecialchars($item['titulo']); ?></h3>
                        <p><strong>Vendedor:</strong> <?php echo htmlspecialchars($item['vendedor_nome']); ?></p>
                        <p class="preco">R$ <?php echo number_format($item['valor_total'], 2, ',', '.'); ?></p>
                        <p><strong>Status:</strong> <span style="color: #00ff66;"><?php echo htmlspecialchars($item['status']); ?></span></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>