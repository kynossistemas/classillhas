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
                $erro = "E-mail não encontrado!";
            } elseif (!password_verify($senha, $usuario['senha_hash'])) {
                $erro = "Senha incorreta!";
            } else {
                // Sucesso - Guarda dados na Sessão
                $_SESSION['usuario_id']     = $usuario['id'];
                $_SESSION['usuario_nome']   = $usuario['nome'];
                $_SESSION['usuario_perfil'] = $usuario['perfil'];

                // REDIRECIONAMENTO INTELIGENTE POR PERFIL:
                switch ($usuario['perfil']) {
                    case 'SUPER_ADMIN':
                        header('Location: admin_master.php');
                        exit;

                    case 'ADMIN_ESCOLA':
                        header('Location: dashboard_escola.php');
                        exit;

                    case 'PROFESSOR':
                    case 'VISUALIZADOR':
                        header('Location: ver_cronograma.php');
                        exit;

                    default:
                        header('Location: dashboard_escola.php');
                        exit;
                }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassIlhas - Acesse sua Conta</title>
    
    <!-- CSS Global e Específico -->
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>

    <main class="login-card">
        <!-- Cabeçalho do Card -->
        <header class="login-header">
            <a href="index.html" class="logo-link" title="Voltar para a página inicial">
                <img src="img/system/classilhas_logo.png" alt="ClassIlhas Logo">
            </a>
            <span class="badge-secure">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                Ambiente Seguro
            </span>
        </header>

        <section class="login-body">
            <p class="welcome-text">Bem-vindo de volta!</p>
            <h1 class="title">Acesse sua Conta</h1>

            <?php if (!empty($erro)): ?>
                <div class="alert" role="alert">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span><?php echo htmlspecialchars($erro); ?></span>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="login-form">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" placeholder="seuemail@escola.com" value="<?php echo htmlspecialchars($email_digitado); ?>" required autofocus>
                </div>

                <div class="form-group">
                    <div class="label-row">
                        <label for="senha">Senha</label>
                        <a href="#" class="forgot-link">Esqueceu a senha?</a>
                    </div>
                    <div class="password-wrapper">
                        <input type="password" id="senha" name="senha" placeholder="Sua senha de acesso" required>
                        <button type="button" class="toggle-btn" onclick="toggleSenha()" aria-label="Mostrar ou esconder senha">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    Entrar no Sistema
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </button>
            </form>
        </section>

        <!-- Rodapé do Card -->
        <footer class="login-footer">
            <p>Precisa de suporte? Entre em contato com a secretaria de sua escola.</p>
        </footer>
    </main>

    <script src="js/script.js"></script> 
</body>
</html>