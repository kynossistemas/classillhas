<?php
session_start();

// Se o usuário tentar entrar direto sem logar, chuta ele de volta pro login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>ClassIlhas - Dashboard</title>
</head>
<body>
    <h1>Bem-vindo ao ClassIlhas!</h1>
    <p>Olá, <strong><?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></strong>!</p>
    <p>Seu perfil é: <strong><?php echo htmlspecialchars($_SESSION['usuario_perfil']); ?></strong></p>
</body>
</html>