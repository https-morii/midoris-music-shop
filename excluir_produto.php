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
    $usuario_id = $_SESSION['usuario_id'];
    $usuario_tipo = $_SESSION['usuario_tipo'];

    try {
        // Busca o produto para verificar permissão e pegar o nome da imagem
        $sql_busca = "SELECT * FROM produtos WHERE id = :id";
        $stmt_busca = $pdo->prepare($sql_busca);
        $stmt_busca->execute([':id' => $produto_id]);
        $produto = $stmt_busca->fetch(PDO::FETCH_ASSOC);

        if ($produto) {
            // Apenas o dono do produto ou um admin pode excluir
            if ($produto['usuario_id'] == $usuario_id || $usuario_tipo == 'admin') {
                
                // Remove a imagem da pasta se existir
                if (!empty($produto['imagem'])) {
                    $caminho_imagem = __DIR__ . '/uploads/' . $produto['imagem'];
                    if (file_exists($caminho_imagem)) {
                        unlink($caminho_imagem);
                    }
                }

                // Exclui o produto do banco
                $sql_delete = "DELETE FROM produtos WHERE id = :id";
                $stmt_delete = $pdo->prepare($sql_delete);
                $stmt_delete->execute([':id' => $produto_id]);
            }
        }
    } catch (PDOException $e) {
        // Trata erro de chave estrangeira (caso já tenha pedidos vinculados)
        header("Location: index.php?erro=produto_com_pedidos");
        exit();
    }
}

header("Location: index.php");
exit();
?>