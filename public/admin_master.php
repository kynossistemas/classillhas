<?php
session_start();
require_once __DIR__ . '/../config/conexao.php';

// Proteção da Página: Apenas SUPER_ADMIN pode acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_perfil'] !== 'SUPER_ADMIN') {
    header('Location: login.php');
    exit;
}

$mensagem = '';
$tipo_mensagem = '';

// 1. Processar Ações (Cadastrar, Editar, Excluir)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    
    // --- CADASTRO DE USUÁRIO ---
    if ($_POST['acao'] === 'cadastrar_usuario') {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = trim($_POST['senha'] ?? '');
        $perfil = $_POST['perfil'] ?? 'ADMIN_ESCOLA';

        if (!empty($nome) && !empty($email) && !empty($senha)) {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            try {
                $stmt = $pdo->prepare("INSERT INTO escolas.usuarios (nome, email, senha_hash, perfil) VALUES (:nome, :email, :senha_hash, :perfil)");
                $stmt->execute([
                    'nome'       => $nome,
                    'email'      => $email,
                    'senha_hash' => $senha_hash,
                    'perfil'     => $perfil
                ]);
                $mensagem = "Usuário <strong>" . htmlspecialchars($nome) . "</strong> cadastrado com sucesso!";
                $tipo_mensagem = "sucesso";
            } catch (PDOException $e) {
                if ($e->getCode() == 23505) { // Erro de e-mail duplicado
                    $mensagem = "O e-mail '$email' já está em uso por outro usuário.";
                } else {
                    $mensagem = "Erro ao cadastrar: " . $e->getMessage();
                }
                $tipo_mensagem = "erro";
            }
        } else {
            $mensagem = "Preencha todos os campos obrigatórios do formulário.";
            $tipo_mensagem = "erro";
        }
    }

    // --- EDIÇÃO DE USUÁRIO ---
    if ($_POST['acao'] === 'editar_usuario') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $perfil = $_POST['perfil'] ?? '';
        $senha = trim($_POST['senha'] ?? '');

        if ($id && !empty($nome) && !empty($email) && !empty($perfil)) {
            try {
                if (!empty($senha)) {
                    // Atualiza incluindo a nova senha
                    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE escolas.usuarios SET nome = :nome, email = :email, perfil = :perfil, senha_hash = :senha_hash WHERE id = :id");
                    $stmt->execute([
                        'nome'       => $nome,
                        'email'      => $email,
                        'perfil'     => $perfil,
                        'senha_hash' => $senha_hash,
                        'id'         => $id
                    ]);
                } else {
                    // Atualiza sem alterar a senha
                    $stmt = $pdo->prepare("UPDATE escolas.usuarios SET nome = :nome, email = :email, perfil = :perfil WHERE id = :id");
                    $stmt->execute([
                        'nome'   => $nome,
                        'email'  => $email,
                        'perfil' => $perfil,
                        'id'     => $id
                    ]);
                }
                $mensagem = "Usuário atualizado com sucesso!";
                $tipo_mensagem = "sucesso";
            } catch (PDOException $e) {
                if ($e->getCode() == 23505) {
                    $mensagem = "O e-mail '$email' já está em uso por outro usuário.";
                } else {
                    $mensagem = "Erro ao atualizar usuário: " . $e->getMessage();
                }
                $tipo_mensagem = "erro";
            }
        } else {
            $mensagem = "Dados inválidos para edição.";
            $tipo_mensagem = "erro";
        }
    }

    // --- EXCLUSÃO DE USUÁRIO ---
    if ($_POST['acao'] === 'excluir_usuario') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        // Não permite que o Super Admin logado exclua a própria conta
        if ($id === (int)$_SESSION['usuario_id']) {
            $mensagem = "Você não pode excluir sua própria conta enquanto estiver logado.";
            $tipo_mensagem = "erro";
        } elseif ($id) {
            try {
                $stmt = $pdo->prepare("DELETE FROM escolas.usuarios WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $mensagem = "Usuário excluído com sucesso!";
                $tipo_mensagem = "sucesso";
            } catch (PDOException $e) {
                $mensagem = "Erro ao excluir usuário: " . $e->getMessage();
                $tipo_mensagem = "erro";
            }
        }
    }
}

// 2. Parâmetros de Filtro
$busca_termo  = trim($_GET['busca'] ?? '');
$busca_perfil = $_GET['perfil_filtro'] ?? '';

// 3. Buscar Dados para Estatísticas e Tabelas
try {
    // Estatísticas
    $total_usuarios = $pdo->query("SELECT COUNT(*) FROM escolas.usuarios")->fetchColumn();
    $total_escolas  = $pdo->query("SELECT COUNT(*) FROM escolas.usuarios WHERE perfil = 'ADMIN_ESCOLA'")->fetchColumn();
    $total_profs    = $pdo->query("SELECT COUNT(*) FROM escolas.professores")->fetchColumn();

    // Montagem da query com filtros
    $sql = "SELECT id, nome, email, perfil, criado_em FROM escolas.usuarios WHERE 1=1";
    $params = [];

    if (!empty($busca_termo)) {
        $sql .= " AND (nome ILIKE :busca OR email ILIKE :busca)";
        $params['busca'] = "%{$busca_termo}%";
    }

    if (!empty($busca_perfil)) {
        $sql .= " AND perfil = :perfil_filtro";
        $params['perfil_filtro'] = $busca_perfil;
    }

    $sql .= " ORDER BY id DESC";

    $stmt_users = $pdo->prepare($sql);
    $stmt_users->execute($params);
    $usuarios = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao carregar dados do painel: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassIlhas - Painel Super Admin</title>

    <!-- 1. CSS Global (Variáveis, resets, botões básicos) -->
    <link rel="stylesheet" href="css/global.css">
    
    <!-- 2. CSS Específico da Landing Page -->
    <link rel="stylesheet" href="css/admin_master.css">

</head>
<body>

    <nav class="navbar">
        <h1>ClassIlhas • Painel Master</h1>
        <div class="user-info">
            <span>Olá, <strong><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></strong></span>
            <a href="logout.php" class="btn-logout">Sair</a>
        </div>
    </nav>

    <div class="container">

        <!-- Cards de Estatísticas -->
        <div class="cards-grid">
            <div class="card">
                <h3>Total de Usuários</h3>
                <div class="number"><?php echo $total_usuarios; ?></div>
            </div>
            <div class="card" style="border-left-color: #38a169;">
                <h3>Escolas / Admins Cadastrados</h3>
                <div class="number"><?php echo $total_escolas; ?></div>
            </div>
            <div class="card" style="border-left-color: #dd6b20;">
                <h3>Professores Registrados</h3>
                <div class="number"><?php echo $total_profs; ?></div>
            </div>
        </div>

        <!-- Formulário para Cadastrar Novo Administrador/Escola -->
        <div class="section-box">
            <h2>Cadastrar Novo Administrador / Escola</h2>

            <?php if (!empty($mensagem)): ?>
                <div class="alert <?php echo $tipo_mensagem; ?>">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <form action="admin_master.php" method="POST">
                <input type="hidden" name="acao" value="cadastrar_usuario">
                <div class="form-grid">
                    <div class="field">
                        <label for="nome">Nome Completo / Nome da Escola:</label>
                        <input type="text" id="nome" name="nome" placeholder="Ex: Escola Santa Maria ou João Silva" required>
                    </div>

                    <div class="field">
                        <label for="email">E-mail de Acesso:</label>
                        <input type="email" id="email" name="email" placeholder="admin@escola.com" required>
                    </div>

                    <div class="field">
                        <label for="senha">Senha Provisória:</label>
                        <div class="password-wrapper">
                            <input type="password" id="senha" name="senha" required>
                            <button type="button" class="btn-toggle-pass toggle-btn" onclick="toggleSenha('senha', this)" aria-label="Mostrar/Esconder senha">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                    </div>

                    <div class="field">
                        <label for="perfil">Perfil do Usuário:</label>
                        <select id="perfil" name="perfil" required>
                            <option value="ADMIN_ESCOLA" selected>Administrador de Escola</option>
                            <option value="SUPER_ADMIN">Super Admin (Membro da Equipe)</option>
                            <option value="PROFESSOR">Professor</option>
                            <option value="VISUALIZADOR">Visualizador (Aluno)</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Cadastrar Usuário</button>
            </form>
        </div>

        <!-- Tabela e Filtros de Usuários Existentes -->
        <div class="section-box">
            <h2>Usuários Cadastrados no Sistema</h2>

            <!-- 1. FILTRAGEM DE USUÁRIOS -->
            <form method="GET" action="admin_master.php" class="filter-grid">
                <div class="field">
                    <label for="busca">Buscar por Nome ou E-mail:</label>
                    <input type="text" id="busca" name="busca" value="<?php echo htmlspecialchars($busca_termo); ?>" placeholder="Digite nome ou e-mail...">
                </div>

                <div class="field">
                    <label for="perfil_filtro">Filtrar por Perfil:</label>
                    <select id="perfil_filtro" name="perfil_filtro">
                        <option value="">Todos os Perfis</option>
                        <option value="SUPER_ADMIN" <?php echo $busca_perfil === 'SUPER_ADMIN' ? 'selected' : ''; ?>>Super Admin</option>
                        <option value="ADMIN_ESCOLA" <?php echo $busca_perfil === 'ADMIN_ESCOLA' ? 'selected' : ''; ?>>Administrador de Escola</option>
                        <option value="PROFESSOR" <?php echo $busca_perfil === 'PROFESSOR' ? 'selected' : ''; ?>>Professor</option>
                        <option value="VISUALIZADOR" <?php echo $busca_perfil === 'VISUALIZADOR' ? 'selected' : ''; ?>>Visualizador</option>
                    </select>
                </div>

                <button type="submit" class="btn-filter">Filtrar</button>
                <?php if (!empty($busca_termo) || !empty($busca_perfil)): ?>
                    <a href="admin_master.php" class="btn-filter" style="background-color: #718096;">Limpar Filtros</a>
                <?php endif; ?>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome / Entidade</th>
                        <th>E-mail</th>
                        <th>Perfil</th>
                        <th>Data de Cadastro</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #718096; padding: 20px;">Nenhum usuário encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td>#<?php echo $u['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($u['nome']); ?></strong></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <span class="badge <?php echo $u['perfil']; ?>">
                                        <?php echo $u['perfil']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($u['criado_em'])); ?></td>
                                <td>
                                    <div class="action-btns">
                                        <!-- 2. EDITAR -->
                                        <button class="btn-edit" onclick='abrirModalEdicao(<?php echo json_encode($u); ?>)'>Editar</button>
                                        
                                        <!-- 3. EXCLUIR -->
                                        <form method="POST" action="admin_master.php" onsubmit="return confirm('Tem certeza que deseja excluir o usuário <?php echo htmlspecialchars($u['nome']); ?>?');" style="display:inline;">
                                            <input type="hidden" name="acao" value="excluir_usuario">
                                            <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                            <button type="submit" class="btn-delete">Excluir</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- MODAL DE EDIÇÃO DE USUÁRIO -->
    <div id="modalEdicao" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Editar Usuário</h3>
                <span class="close-modal" onclick="fecharModalEdicao()">&times;</span>
            </div>
            <form action="admin_master.php" method="POST">
                <input type="hidden" name="acao" value="editar_usuario">
                <input type="hidden" id="edit_id" name="id">

                <div class="field" style="margin-bottom: 15px;">
                    <label for="edit_nome">Nome / Entidade:</label>
                    <input type="text" id="edit_nome" name="nome" required>
                </div>

                <div class="field" style="margin-bottom: 15px;">
                    <label for="edit_email">E-mail de Acesso:</label>
                    <input type="email" id="edit_email" name="email" required>
                </div>

                <div class="field" style="margin-bottom: 15px;">
                    <label for="edit_perfil">Perfil do Usuário:</label>
                    <select id="edit_perfil" name="perfil" required>
                        <option value="ADMIN_ESCOLA">Administrador de Escola</option>
                        <option value="SUPER_ADMIN">Super Admin (Membro da Equipe)</option>
                        <option value="PROFESSOR">Professor</option>
                        <option value="VISUALIZADOR">Visualizador (Aluno)</option>
                    </select>
                </div>

                <!-- CAMPO NO MODAL DE EDIÇÃO -->
                <div class="field" style="margin-bottom: 20px;">
                    <label for="edit_senha">Nova Senha (deixe em branco para não alterar):</label>
                    <div class="password-wrapper">
                        <input type="password" id="edit_senha" name="senha" placeholder="Digite apenas se for alterar">
                        <button type="button" class="btn-toggle-pass toggle-btn" onclick="toggleSenha('edit_senha', this)" aria-label="Mostrar/Esconder senha">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit" style="width: 100%;">Salvar Alterações</button>
            </form>
        </div>
    </div>

    <script src="js/script.js"></script> <!--Importando a função toggleSenha-->
    <script>
        // LÓGICA DO MODAL DE EDIÇÃO
        function abrirModalEdicao(usuario) {
            document.getElementById('edit_id').value = usuario.id;
            document.getElementById('edit_nome').value = usuario.nome;
            document.getElementById('edit_email').value = usuario.email;
            document.getElementById('edit_perfil').value = usuario.perfil;
            document.getElementById('edit_senha').value = ''; // Limpa o campo de senha por segurança
            
            document.getElementById('modalEdicao').style.display = 'flex';
        }

        function fecharModalEdicao() {
            document.getElementById('modalEdicao').style.display = 'none';
        }

        // Fechar modal ao clicar fora dele
        window.onclick = function(event) {
            const modal = document.getElementById('modalEdicao');
            if (event.target === modal) {
                fecharModalEdicao();
            }
        }
    </script>
</body>
</html>