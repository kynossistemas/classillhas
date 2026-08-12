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