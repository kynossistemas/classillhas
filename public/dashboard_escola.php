<?php
session_start();
require_once __DIR__ . '/../config/conexao.php';

// Proteção de Acesso
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_perfil'] !== 'ADMIN_ESCOLA') {
    header('Location: login.php');
    exit;
}

$mensagem = '';
$tipo_mensagem = '';
$usuario_id = $_SESSION['usuario_id'];

// Processar Atualização de Perfil / Senha (Modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    if ($_POST['acao'] === 'atualizar_perfil') {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha_nova = trim($_POST['senha_nova'] ?? '');

        if (!empty($nome) && !empty($email)) {
            try {
                if (!empty($senha_nova)) {
                    $senha_hash = password_hash($senha_nova, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE escolas.usuarios SET nome = :nome, email = :email, senha_hash = :senha_hash WHERE id = :id");
                    $stmt->execute(['nome' => $nome, 'email' => $email, 'senha_hash' => $senha_hash, 'id' => $usuario_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE escolas.usuarios SET nome = :nome, email = :email WHERE id = :id");
                    $stmt->execute(['nome' => $nome, 'email' => $email, 'id' => $usuario_id]);
                }
                $_SESSION['usuario_nome'] = $nome;
                $mensagem = "Dados da escola atualizados com sucesso!";
                $tipo_mensagem = "sucesso";
            } catch (PDOException $e) {
                $mensagem = "Erro ao atualizar dados: " . $e->getMessage();
                $tipo_mensagem = "erro";
            }
        }
    }
}

// Buscar Dados Atuais da Escola
$stmt = $pdo->prepare("SELECT nome, email FROM escolas.usuarios WHERE id = :id");
$stmt->execute(['id' => $usuario_id]);
$dados_escola = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassIlhas • Painel Gestão Escolar</title>
    <!-- CSS Global primeiro, depois o do Dashboard -->
    <link rel="stylesheet" href="css/global.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/dashboard_escola.css?v=<?php echo time(); ?>">
</head>
<body>

    <nav class="navbar">
        <h1>ClassIlhas • Gestão Escolar</h1>
        <div class="user-info">
            <span>Escola: <strong><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></strong></span>
            <button class="btn btn-filter" onclick="abrirModalConfig()">⚙️ Configurações</button>
            <a href="logout.php" class="btn btn-logout">Sair</a>
        </div>
    </nav>

    <div class="container">
        <?php if (!empty($mensagem)): ?>
            <div class="alert <?php echo $tipo_mensagem; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <!-- Sidebar / Menu Lateral de Abas -->
            <aside class="sidebar-nav">
                <button class="nav-btn active" onclick="mudarAba('visao-geral', this)">📊 Visão Geral</button>
                <button class="nav-btn" onclick="mudarAba('professores', this)">👨‍🏫 Professores</button>
                <button class="nav-btn" onclick="mudarAba('disciplinas', this)">📚 Disciplinas</button>
                <button class="nav-btn" onclick="mudarAba('turmas', this)">🏫 Turmas & Parâmetros</button>
                <button class="nav-btn" onclick="mudarAba('cronograma', this)">📅 Grade Horária</button>
            </aside>

            <!-- Área Dinâmica das Abas -->
            <main class="content-area">
                <div id="aba-visao-geral" class="tab-content active">
                    <div class="section-box">
                        <h2>Painel de Controle</h2>
                        <p>Selecione um dos módulos ao lado para gerenciar sua grade e professores.</p>
                    </div>
                </div>

                <div id="aba-professores" class="tab-content">
                    <div class="section-box">
                        <h2>Gestão de Professores</h2>
                        <p>Módulo de cadastro e disponibilidade dos professores em construção...</p>
                    </div>
                </div>

                <div id="aba-disciplinas" class="tab-content">
                    <div class="section-box">
                        <h2>Cadastro de Disciplinas / Matérias</h2>
                        <p>Módulo de cadastro de disciplinas em construção...</p>
                    </div>
                </div>

                <div id="aba-turmas" class="tab-content">
                    <div class="section-box">
                        <h2>Configuração de Turmas e Turnos</h2>
                        <p>Módulo de séries, sufixos e cursos extras em construção...</p>
                    </div>
                </div>

                <div id="aba-cronograma" class="tab-content">
                    <div class="section-box">
                        <h2>Montagem da Grade Horária</h2>
                        <p>Gerador da matriz semanal de aulas em construção...</p>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- MODAL DE CONFIGURAÇÕES -->
    <div id="modalConfig" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Configurações da Conta</h3>
                <span class="close-modal" onclick="fecharModalConfig()">&times;</span>
            </div>
            <form action="dashboard_escola.php" method="POST">
                <input type="hidden" name="acao" value="atualizar_perfil">
                <div class="field">
                    <label for="nome">Nome da Escola / Instituição:</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($dados_escola['nome']); ?>" required>
                </div>
                <div class="field">
                    <label for="email">E-mail de Acesso:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($dados_escola['email']); ?>" required>
                </div>
                <div class="field">
                    <label for="senha_config">Nova Senha (deixe em branco para não alterar):</label>
                    <div class="password-wrapper">
                        <input type="password" id="senha_config" name="senha_nova" placeholder="Nova senha">
                        <button type="button" class="btn-toggle-pass" onclick="toggleSenha('senha_config', this)">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-submit" style="width: 100%; margin-top: 15px;">Salvar Alterações</button>
            </form>
        </div>
    </div>

    <script src="js/script.js"></script>
    <script>
        function mudarAba(nomeAba, btn) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
            document.getElementById('aba-' + nomeAba).classList.add('active');
            btn.classList.add('active');
        }

        function abrirModalConfig() {
            document.getElementById('modalConfig').style.display = 'flex';
        }

        function fecharModalConfig() {
            document.getElementById('modalConfig').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('modalConfig');
            if (event.target === modal) fecharModalConfig();
        }
    </script>
</body>
</html>