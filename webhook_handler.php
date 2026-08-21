<?php
// webhook_handler.php - O receptor de mensagens
header('Content-Type: application/json');

// 1. Captura o JSON enviado pela Evolution
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) exit;

// 2. Verifica se o evento é MESSAGES_UPSERT
if ($data['event'] === 'messages.upsert') {
    
    $payload = $data['data'];
    $isGroup = isset($payload['key']['remoteJid']) && strpos($payload['key']['remoteJid'], '@g.us') !== false;
    $fromMe = $payload['key']['fromMe'] ?? false;

    // 3. Filtro de Segurança: Ignora mensagens enviadas por você mesmo ou grupos (se preferir)
    if ($fromMe) exit; 

    // Dados principais da mensagem
    $remoteJid = $payload['key']['remoteJid']; // Número do cliente
    $pushName = $payload['pushName'] ?? 'Cliente';
    $messageText = "";

    // Captura o texto (pode vir em diferentes formatos dependendo se é texto puro, imagem com legenda, etc)
    if (isset($payload['message']['conversation'])) {
        $messageText = $payload['message']['conversation'];
    } elseif (isset($payload['message']['extendedTextMessage']['text'])) {
        $messageText = $payload['message']['extendedTextMessage']['text'];
    }

    // --- AQUI VOCÊ FAZ O QUE QUISER COM A MENSAGEM ---
    
    // Exemplo: Salvar num log ou disparar um alerta
    file_put_contents('mensagens_recebidas.log', "[".date('Y-m-d H:i:s')."] $pushName ($remoteJid): $messageText" . PHP_EOL, FILE_APPEND);

    // Exemplo: Se o cliente digitar "Suporte", você poderia responder via API
}

echo json_encode(["status" => "success"]);