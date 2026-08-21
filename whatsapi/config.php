<?php
// Configura cookies de sessão para serem mais seguros
ini_set('session.cookie_httponly', 1); // Impede acesso via JavaScript (evita roubo de sessão)
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // Ative se usar HTTPS (obrigatório para produção)

// Cabeçalhos de Segurança
header("X-Frame-Options: DENY"); // Impede que seu site seja colocado em um iframe (evita Clickjacking)
header("X-Content-Type-Options: nosniff");
// config.php
// Inicia a sessão para controle de login dos usuários
session_start();

date_default_timezone_set('America/Sao_Paulo');

$base_url = getenv('API_BASE_URL');
$api_key = getenv('MASTER_API_KEY');

// Defina a URL base da API do whatsmiau
define('API_BASE_URL', $base_url);
// Defina a sua Chave de API
define('MASTER_API_KEY', $api_key);

$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'banco_local';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

define('DB_HOST', $host);
define('DB_NAME', $dbname);
define('DB_USER', $user);
define('DB_PASS', $pass);
?>
