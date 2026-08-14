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
    <title>ClassIlhas - Login</title>
    
    <!-- 1. CSS Global (Variáveis, resets, botões básicos) -->
    <link rel="stylesheet" href="css/global.css">
    
    <!-- 2. CSS Específico da Landing Page -->
    <link rel="stylesheet" href="css/login.css">

</head>
<body>

    <div class="main-container">
        <!-- COLUNA ESQUERDA: Formulário de Login -->
        <div class="login-section">
            <div class="header-logo">
                <img src="img/classilhas_logo.png" alt="Logo ClassIlhas">
                <div class="top-badge">AMBIENTE SEGURO</div>
            </div>

            <p class="welcome-text">Bem-vindo de volta</p>
            <h2 class="title">ACESSE SUA CONTA</h2>

            <?php if (!empty($erro)): ?>
                <div class="alert"><?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="email">E-mail ou Usuário</label>
                    <input type="email" id="email" name="email" placeholder="Ex: nome@escola.com" value="<?php echo htmlspecialchars($email_digitado); ?>" required>
                </div>

                <div class="form-group">
                    <label for="senha">Senha</label>
                    <div class="password-wrapper">
                        <input type="password" id="senha" name="senha" placeholder="Sua senha segura" required>
                        <button type="button" class="toggle-btn" onclick="toggleSenha()" aria-label="Mostrar/Esconder senha">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    Acessar o Sistema 
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </button>
                
                <a href="#" class="btn-secondary">
                    &#128274; Esqueci minha senha
                </a>
            </form>

            <div class="help-box">
                Caso você não consiga acessar o sistema, verifique suas credenciais ou entre em contato com a secretaria. <a href="#">Clique aqui para ajuda.</a>
            </div>

            <div class="help-box" style="text-align: center;">
                Suporte Técnico: <strong>(00) 00000-0000</strong>
            </div>
        </div>

        <!-- COLUNA DIREITA: Painel de Informações -->
        <div class="info-section">
            <div class="info-header">
                <div class="info-badge">NOVIDADES</div>
                <span class="info-header-text">Plataforma ClassIlhas atualizada</span>
            </div>

            <h3>NOVA MANEIRA DE ACESSAR O CLASSILHAS!</h3>
            <p class="sub-text">Uma interface mais limpa, focada na usabilidade e comunicação rápida para alunos e professores.</p>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">&#9889;</div>
                    <div class="feature-content">
                        <h4>AGILIDADE</h4>
                        <p>Acesso rápido ao conteúdo das aulas e notas.</p>
                    </div>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">&#128172;</div>
                    <div class="feature-content">
                        <h4>SUPORTE</h4>
                        <p>Equipe pronta para ajudar via chat ou e-mail.</p>
                    </div>
                </div>
            </div>

            <div class="social-section">
                <div class="social-header">
                    <div class="social-title-group">
                        <h4>COMUNICAÇÃO</h4>
                        <h3>FIQUE POR DENTRO</h3>
                    </div>
                    <div class="online-status">
                        <svg width="10" height="10" viewBox="0 0 10 10"><circle cx="5" cy="5" r="5" fill="white"/></svg>
                        ONLINE
                    </div>
                </div>
                <p>Acompanhe comunicados, eventos e calendários escolares diretamente no portal.</p>
                <div class="social-buttons">
                    <a href="#" class="btn-social">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/e/ef/Youtube_logo_icon_ios.svg" alt="YouTube" width="16">
                        Tutorial
                    </a>
                    <a href="#" class="btn-social">
                        &#128196; Blog
                    </a>
                </div>
            </div>

            <div class="info-footer">
                Portal ClassIlhas • Segurança e privacidade dos seus dados.
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>   

</body>
</html>