<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário está logado e se é vendedor ou admin
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] != 'vendedor' && $_SESSION['usuario_tipo'] != 'admin')) {
    header("Location: index.php");
    exit();
}

$mensagem = "";

// Busca as categorias no banco de dados
try {
    $stmt_cat = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC");
    $categorias = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categorias = [];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = trim($_POST['titulo']);
    $categoria_id = $_POST['categoria_id'];
    $preco = $_POST['preco'];
    $estado = $_POST['estado_conservacao'];
    $estoque = $_POST['estoque'];
    $descricao = trim($_POST['descricao']);
    $usuario_id = $_SESSION['usuario_id'];
    $nome_imagem = null;

    // Processamento do upload da imagem
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($extensao, $extensoes_permitidas)) {
            // Garante que o diretório de uploads existe
            $diretorio_uploads = __DIR__ . '/uploads/';
            if (!is_dir($diretorio_uploads)) {
                mkdir($diretorio_uploads, 0777, true);
            }

            // Gera um nome único para a imagem para evitar sobreposição
            $nome_imagem = uniqid() . '.' . $extensao;
            $pasta_destino = $diretorio_uploads . $nome_imagem;

            if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $pasta_destino)) {
                $mensagem = "Erro ao salvar a imagem na pasta uploads.";
                $nome_imagem = null;
            }
        } else {
            $mensagem = "Formato de imagem inválido! Use JPG, PNG, GIF ou WEBP.";
        }
    }

    if (empty($mensagem)) {
        try {
            $sql = "INSERT INTO produtos (usuario_id, categoria_id, titulo, descricao, preco, estado_conservacao, estoque, imagem) 
                    VALUES (:usuario_id, :categoria_id, :titulo, :descricao, :preco, :estado, :estoque, :imagem)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':usuario_id' => $usuario_id,
                ':categoria_id' => $categoria_id,
                ':titulo' => $titulo,
                ':descricao' => $descricao,
                ':preco' => $preco,
                ':estado' => $estado,
                ':estoque' => $estoque,
                ':imagem' => $nome_imagem
            ]);

            $mensagem = "Produto cadastrado com sucesso no garimpo!";
        } catch (PDOException $e) {
            $mensagem = "Erro ao cadastrar produto: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Anunciar Produto - Midori’s Music Shop</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <header>
        <h1>Midori’s Music Shop 🎧⚡</h1>
        <p><a href="index.php">← Voltar para a Página Inicial</a></p>
    </header>

    <main>
        <form action="cadastrar_produto.php" method="POST" enctype="multipart/form-data">
            <h2>Anunciar Item no Garimpo 🎧</h2>

            <?php if (!empty($mensagem)): ?>
                <p style="color: <?php echo (strpos($mensagem, 'sucesso') !== false) ? '#00ff66' : '#ff4d4d'; ?>; margin-bottom: 15px;">
                    <strong><?php echo $mensagem; ?></strong>
                </p>
            <?php endif; ?>

            <label>Título do Anúncio:</label>
            <input type="text" name="titulo" placeholder="Ex: Toca-Discos Vintage CCE" required>

            <label>Categoria:</label>
            <select name="categoria_id" required>
                <option value="">Selecione uma categoria</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nome']); ?></option>
                <?php endforeach; ?>
            </select>

            <label>Preço (R$):</label>
            <input type="number" step="0.01" name="preco" placeholder="150.00" required>

            <label>Estado de Conservação:</label>
            <select name="estado_conservacao" required>
                <option value="Novo">Novo</option>
                <option value="Seminovo">Seminovo</option>
                <option value="Usado - Excelente">Usado - Excelente</option>
                <option value="Usado - Com marcas">Usado - Com marcas</option>
                <option value="Para Restauro/Peças">Para Restauro/Peças</option>
            </select>

            <label>Quantidade em Estoque:</label>
            <input type="number" name="estoque" value="1" min="1" required>

            <label>Imagem do Produto:</label>
            <input type="file" name="imagem" accept="image/*">

            <label>Descrição do Item:</label>
            <textarea name="descricao" rows="4" placeholder="Conte detalhes sobre o estado do item, funcionamento, etc."></textarea>

            <button type="submit">Cadastrar Produto</button>
        </form>
    </main>
</body>
</html>