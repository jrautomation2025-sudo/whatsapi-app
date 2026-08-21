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

// Defina a URL base da API do whatsmiau
define('API_BASE_URL', 'https://whatsapi.jrtec.com.br/v1');

// Defina a sua Chave de API
define('MASTER_API_KEY', '7acBzr07UsAcRZ2lmtFyMJKak3Be9ijiaNUe9vUndziAO9ie');

// Configurações do Banco de Dados MySQL (Preencha com os dados da Hostinger)
define('DB_HOST', 'localhost'); // Na Hostinger, geralmente é localhost
define('DB_NAME', 'u134815491_whatsapi'); // Substitua pelo nome do banco criado
define('DB_USER', 'u134815491_whatsapi'); // Substitua pelo usuário do banco
define('DB_PASS', 'CteUfzvByN1Xv4I76IQix1iFo1HumxCt'); // Substitua pela senha do banco
?>