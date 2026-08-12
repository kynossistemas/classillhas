<?php
session_start();
require_once __DIR__ . '/../config/conexao.php';

$erro = '';
$email_digitado = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_digitado = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (!empty($email_digitado) && !empty($senha)) {
        try {
            // Busca o usuário pelo e-mail
            $stmt = $pdo->prepare("SELECT * FROM escolas.usuarios WHERE email = :email");
            $stmt->execute(['email' => $email_digitado]);
            $usuario = $stmt->fetch();

            if (!$usuario) {
                // Diagnóstico 1: O e-mail não existe no banco
                $erro = "E-mail não encontrado!";
            } elseif (!password_verify($senha, $usuario['senha_hash'])) {
                // Diagnóstico 2: O e-mail existe, mas a senha está incorreta
                $erro = "Senha incorreta!";
            } else {
                // Sucesso
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_perfil'] = $usuario['perfil'];

                header('Location: dashboard.php');
                exit;
            }
        } catch (PDOException $e) {
            $erro = "Erro no banco de dados: " . $e->getMessage();
        }
    } else {
        $erro = "Preencha todos os campos!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>ClassIlhas - Login</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 320px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 8px; box-sizing: border-box; }
        .password-container { display: flex; gap: 5px; }
        .password-container input { flex: 1; }
        .toggle-btn { padding: 8px 12px; background: #e0e0e0; border: 1px solid #ccc; border-radius: 4px; cursor: pointer; }
        button[type="submit"] { width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-top: 10px; }
        .alert { color: #d9534f; background: #fdf7f7; border: 1px solid #d9534f; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
    </style>
</head>
<body>

    <div class="login-card">
        <h2>Entrar no ClassIlhas</h2>

        <!-- Exibe o erro na própria página sem recarregar do zero -->
        <?php if (!empty($erro)): ?>
            <div class="alert"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email">E-mail:</label>
                <!-- O value mantêm o e-mail preenchido se der erro -->
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email_digitado); ?>" required>
            </div>

            <div class="form-group">
                <label for="senha">Senha:</label>
                <div class="password-container">
                    <input type="password" id="senha" name="senha" required>
                    <button type="button" class="toggle-btn" onclick="toggleSenha()">👁️</button>
                </div>
            </div>

            <button type="submit">Entrar</button>
        </form>
    </div>

    <script>
        function toggleSenha() {
            const inputSenha = document.getElementById('senha');
            if (inputSenha.type === 'password') {
                inputSenha.type = 'text';
            } else {
                inputSenha.type = 'password';
            }
        }
    </script>

</body>
</html>