<?php
// public/cadastrar_usuario.php
session_start();
require_once __DIR__ . '/../config/conexao.php';

// Proteção: Apenas o ADMIN pode acessar esta página
if (!isset($_SESSION['usuario_perfil']) || $_SESSION['usuario_perfil'] !== 'ADMIN') {
    die("Acesso negado! Apenas Administradores podem gerar usuários.");
}

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha_pura = trim($_POST['senha']);
    $perfil = $_POST['perfil'];

    if (!empty($nome) && !empty($email) && !empty($senha_pura)) {
        // 1. Gera a hash segura no PHP
        $senha_hash = password_hash($senha_pura, PASSWORD_DEFAULT);

        try {
            // 2. Insere o novo usuário no schema 'escolas' usando Prepared Statements
            $stmt = $pdo->prepare("INSERT INTO escolas.usuarios (nome, email, senha_hash, perfil) VALUES (:nome, :email, :senha_hash, :perfil)");
            $stmt->execute([
                'nome'       => $nome,
                'email'      => $email,
                'senha_hash' => $senha_hash,
                'perfil'     => $perfil
            ]);

            $mensagem = "<p style='color: green;'>✅ Usuário <b>$nome</b> ($email) cadastrado com sucesso!</p>";
        } catch (PDOException $e) {
            if ($e->getCode() == 23505) { // Código de erro do PostgreSQL para e-mail duplicado
                $mensagem = "<p style='color: red;'>❌ O e-mail '$email' já está cadastrado!</p>";
            } else {
                $mensagem = "<p style='color: red;'>Erro no banco: " . $e->getMessage() . "</p>";
            }
        }
    } else {
        $mensagem = "<p style='color: red;'>Preencha todos os campos!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Novo Usuário - ClassIlhas</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f4f4f9; }
        .box { background: white; padding: 20px; max-width: 400px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .field { margin-bottom: 12px; }
        .field label { display: block; font-weight: bold; margin-bottom: 5px; }
        .field input, .field select { width: 100%; padding: 8px; box-sizing: border-box; }
        button { padding: 10px 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
    </style>
</head>
<body>

    <div class="box">
        <h2>Cadastrar Usuário / Professor</h2>
        
        <?php echo $mensagem; ?>

        <form action="cadastrar_usuario.php" method="POST">
            <div class="field">
                <label>Nome Completo:</label>
                <input type="text" name="nome" required>
            </div>

            <div class="field">
                <label>E-mail:</label>
                <input type="email" name="email" required>
            </div>

            <div class="field">
                <label>Senha:</label>
                <input type="password" name="senha" required>
            </div>

            <div class="field">
                <label>Perfil do Usuário:</label>
                <select name="perfil">
                    <option value="PROFESSOR">Professor</option>
                    <option value="ADMIN">Administrador</option>
                    <option value="VISUALIZADOR">Visualizador (Aluno)</option>
                </select>
            </div>

            <button type="submit">Gerar Usuário e Criptografar Senha</button>
        </form>

        <br>
        <a href="dashboard.php">← Voltar ao Dashboard</a>
    </div>

</body>
</html>