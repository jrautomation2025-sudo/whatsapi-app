<?php
// webhook.php
require_once 'database.php';

// Recebe o payload (dados) enviado pelo whatsmiau
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

// Se não houver dados ou não identificar a instância, encerra com sucesso (para não travar a API)
if (!$data || !isset($data['instance'])) {
    http_response_code(200);
    exit;
}

$instanceName = $data['instance'];

try {
    // Busca no banco de dados se essa instância possui uma URL de webhook configurada pelo cliente
    $stmt = $pdo->prepare("SELECT webhook_url FROM instances WHERE instance_name = ?");
    $stmt->execute([$instanceName]);
    $instance = $stmt->fetch();

    if ($instance && !empty($instance['webhook_url'])) {
        // Usa cURL para encaminhar (fazer um POST) do mesmo payload para o n8n ou sistema do cliente
        $ch = curl_init($instance['webhook_url']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload)
        ]);
        
        // Executa o disparo e fecha a conexão
        curl_exec($ch);
        curl_close($ch);
    }
} catch (Exception $e) {
    // Registra erro em log interno se necessário, mas sempre responde 200 para a API de origem
    error_log("Erro no Roteador de Webhook: " . $e->getMessage());
}

// Responde 200 OK para o whatsmiau saber que recebemos a mensagem
http_response_code(200);
?>