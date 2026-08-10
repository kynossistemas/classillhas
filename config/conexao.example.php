<?php
// config/conexao.php

$host = 'localhost';
$port = '5432';
$dbname = 'classilhas_db';
$user = 'postgres';
$password = 'sua_senha_baby'; // Aqui vai a senha que tu configurou lá no pgAdmin meu brother

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Define o schema padrão para escolas
    $pdo->exec("SET search_path TO escolas");

} catch (PDOException $e) {
    die("Erro ao conectar com o banco de dados do ClassIlhas: " . $e->getMessage());
}