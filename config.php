<?php
// config.php
// Configurações de conexão com o banco de dados usando PDO
define('DB_HOST', 'localhost');
define('DB_NAME', 'webmaster_universitario');
define('DB_USER', 'webmaster_andrecastro');
define('DB_PASS', 'Omniavincit#8');
date_default_timezone_set('America/Sao_Paulo');
// Tentativa de conexão com o banco de dados
try {
    // Criação da instância PDO
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    
    // Configuração para lançar exceções em caso de erros
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Configuração para desativar emulação de prepared statements, aumentando a segurança
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Configuração para garantir que o charset seja UTF-8
    $pdo->exec("SET NAMES utf8");
} catch (PDOException $e) {
    // Em caso de erro, exibe uma mensagem amigável e interrompe a execução do script
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>
