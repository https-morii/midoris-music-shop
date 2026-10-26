<?php
require_once 'conexao.php';

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $tipo = $_POST['tipo'];

    try {
        $sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (:nome, :email, :senha, :tipo)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':senha' => $senha,
            ':tipo' => $tipo
        ]);
        
        header("Location: login.php?cadastro=sucesso");
        exit();

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $mensagem = "Erro: Este e-mail já está cadastrado.";
        } else {
            $mensagem = "Erro ao cadastrar: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro - Midori’s Music Shop</title>
    <!-- Vincula o CSS estilo Cyber-NuMetal / Frutiger Aero -->
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <header>
        <h1>Midori’s Music Shop 🎧⚡</h1>
    </header>

    <main>
        <form action="cadastro.php" method="POST">
            <h2>Criar Conta</h2>
            
            <?php if (!empty($mensagem)): ?>
                <p style="color: #ff4d4d; margin-bottom: 10px;"><strong><?php echo $mensagem; ?></strong></p>
            <?php endif; ?>

            <label>Nome:</label>
            <input type="text" name="nome" required>

            <label>E-mail:</label>
            <input type="email" name="email" required>

            <label>Senha:</label>
            <input type="password" name="senha" required>

            <label>Tipo de Conta:</label>
            <select name="tipo">
                <option value="cliente">Comprador (Garimpeiro de plantão)</option>
                <option value="vendedor">Vendedor (Quero vender meus itens de áudio)</option>
            </select>

            <button type="submit">Cadastrar</button>

            <br><br>
            <p>Já tem uma conta? <a href="login.php">Faça login aqui</a>.</p>
        </form>
    </main>
</body>
</html>