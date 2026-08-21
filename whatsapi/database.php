<?php
// database.php
require_once 'config.php';

try {
    // Cria a conexão com o banco de dados
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    
    // Configura o PDO para lançar exceções em caso de erros
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Tenta "acordar" a conexão se ela cair
    $pdo->query("SET session wait_timeout=600");
    
    // Define o retorno padrão dos dados como arrays associativos
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Interrompe a execução caso não consiga conectar ao banco
    die("Erro crítico de conexão com o banco de dados. Verifique o config.php.");
}