<?php
session_start();
require_once 'conexao.php';

$mensagem = "";

if (isset($_GET['cadastro']) && $_GET['cadastro'] == 'sucesso') {
    $mensagem = "Cadastro realizado com sucesso! Faça seu login abaixo.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    try {
        $sql = "SELECT * FROM usuarios WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_tipo'] = $usuario['tipo'];

            header("Location: index.php");
            exit();
        } else {
            $mensagem = "E-mail ou senha incorretos.";
        }
    } catch (PDOException $e) {
        $mensagem = "Erro ao realizar login: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - Midori’s Music Shop</title>
    <!-- Vincula o CSS estilo Cyber-NuMetal / Frutiger Aero -->
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <header>
        <h1>Midori’s Music Shop 🎧⚡</h1>
    </header>

    <main>
        <form action="login.php" method="POST">
            <h2>Entrar na Loja</h2>
            
            <?php if (!empty($mensagem)): ?>
                <p style="color: #00ff66; margin-bottom: 10px;"><strong><?php echo $mensagem; ?></strong></p>
            <?php endif; ?>

            <label>E-mail:</label>
            <input type="email" name="email" required>

            <label>Senha:</label>
            <input type="password" name="senha" required>

            <button type="submit">Entrar</button>

            <br><br>
            <p>Ainda não tem conta? <a href="cadastro.php">Cadastre-se aqui</a>.</p>
        </form>
    </main>
</body>
</html>