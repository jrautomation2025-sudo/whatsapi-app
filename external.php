<?php
// external.php - Gateway de API JR Tech Automation compatível com Swagger /v1 do WhatsMiau
error_reporting(E_ALL);
// Definimos como 0 para evitar que Warnings do PHP "vazem" e quebrem o JSON no n8n
ini_set('display_errors', 0); 
require_once 'database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, apikey');

// Buffer de saída para garantir que nada suje o JSON
ob_start();

function sendResponse($code, $data = null) {
    ob_clean(); // Limpa qualquer lixo que tenha saído antes
    http_response_code($code);
    if ($data === null) {
        echo json_encode(new stdClass());
    } else {
        echo json_encode($data);
    }
    exit;
}

function sendError($code, $message, $details = null) {
    $payload = ['error' => $message];
    if ($details !== null) $payload['details'] = $details;
    sendResponse($code, $payload);
}

function getApiKeyFromHeaders() {
    $headers = array_change_key_case(getallheaders(), CASE_LOWER);
    return trim($headers['apikey'] ?? '');
}

function callMotor($method, $endpoint, $data = null) {
    $url = rtrim(API_BASE_URL, '/') . $endpoint;
    $ch = curl_init($url);
    $headers = [
        'apikey: ' . MASTER_API_KEY,
        'Content-Type: application/json'
    ];
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return ['code' => 500, 'body' => ['error' => 'cURL error: ' . $curlError]];
    }

    $decoded = json_decode($res, true);
    return ['code' => $httpCode, 'body' => ($decoded !== null ? $decoded : $res)];
}

function callMotorRaw($method, $endpoint, $data = null) {
    $url = rtrim(API_BASE_URL, '/') . $endpoint;
    $ch = curl_init($url);
    $headers = [
        'apikey: ' . MASTER_API_KEY,
        'Content-Type: application/json'
    ];
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return ['code' => 500, 'body' => null, 'content_type' => null, 'error' => 'cURL error: ' . $curlError];
    }

    return ['code' => $httpCode, 'body' => $res, 'content_type' => $contentType];
}

function requireAuthenticatedUser($pdo) {
    $apiKey = getApiKeyFromHeaders();
    if (!$apiKey) {
        sendError(401, 'Chave de API (apikey) ausente nos cabeçalhos.');
    }

    $stmt = $pdo->prepare('SELECT id, expira_em FROM users WHERE api_token = ?');
    $stmt->execute([$apiKey]);
    $user = $stmt->fetch();

    if (!$user) {
        sendError(403, 'Chave de API inválida.');
    }

    if (strtotime($user['expira_em']) < time()) {
        sendError(403, 'Assinatura expirada. Por favor, renove seu plano no painel.');
    }

    return $user;
}

function getRoutePath() {
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $basePath = dirname($scriptName);

    if ($basePath !== '/' && strpos($requestUri, $basePath) === 0) {
        $route = substr($requestUri, strlen($basePath));
    } else {
        $route = $requestUri;
    }

    if (strpos($route, '/v1') === 0) {
        $route = substr($route, 3);
    }

    if ($route === '') {
        $route = '/';
    }

    if (strlen($route) > 1) {
        $route = rtrim($route, '/');
    }

    return $route;
}

function findInstanceByUser($pdo, $userId, $instanceId) {
    $stmt = $pdo->prepare('SELECT * FROM instances WHERE user_id = ? AND (instance_name = ? OR display_name = ?) LIMIT 1');
    $stmt->execute([$userId, $instanceId, $instanceId]);
    return $stmt->fetch();
}

// Main
try {
    // Configura o timeout da sessão PDO
    $pdo->exec("SET session wait_timeout=600");

    $user = requireAuthenticatedUser($pdo);
    $userId = $user['id'];

    $route = getRoutePath();
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        $input = [];
    }

    $segments = array_values(array_filter(explode('/', trim($route, '/'))));
    $len = count($segments);

    // ---------- Swagger compatible routing ----------
    if ($route === '/' && $method === 'GET') {
        sendResponse(200, [
            'status' => 'ok',
            'api' => 'WhatsMiau JR Tech Automation',
            'version' => '0.4.5', // Atualizada para controle de transação
            'time' => date('c')
        ]);
    }

    // Instance list
    if ($segments[0] === 'instance' && ($method === 'GET') && ($len === 1 || ($len === 2 && $segments[1] === 'fetchInstances'))) {
        $instanceNameFilter = $_GET['instanceName'] ?? null;
        $instanceIdFilter = $_GET['id'] ?? null;

        $sql = 'SELECT * FROM instances WHERE user_id = ?';
        $params = [$userId];

        if ($instanceNameFilter) {
            $sql .= ' AND display_name LIKE ?';
            $params[] = '%' . $instanceNameFilter . '%';
        }
        if ($instanceIdFilter) {
            $sql .= ' AND instance_name LIKE ?';
            $params[] = '%' . $instanceIdFilter . '%';
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $instances = $stmt->fetchAll();

        foreach ($instances as &$item) {
            try {
                $apiRes = callMotor('GET', '/instance/' . $item['instance_name'] . '/status');
                $item['status'] = ($apiRes['code'] >= 200 && $apiRes['code'] < 300)
                    ? ($apiRes['body']['state'] ?? $apiRes['body']['status'] ?? 'disconnected')
                    : 'error';
            } catch (Exception $ex) {
                $item['status'] = 'error';
            }
        }
        sendResponse(200, $instances);
    }

    // /instance/connect/{id} - GET
    if ($segments[0] === 'instance' && $method === 'GET' && $len === 3 && $segments[1] === 'connect') {
        $targetInstanceId = $segments[2];
        $targetInstance = findInstanceByUser($pdo, $userId, $targetInstanceId);
        if (!$targetInstance) sendError(404, 'Instância não encontrada.');
        $apiRes = callMotor('POST', '/instance/' . $targetInstance['instance_name'] . '/connect');
        sendResponse($apiRes['code'] >= 200 && $apiRes['code'] < 300 ? 200 : $apiRes['code'], $apiRes['body']);
    }

    // /instance/connect/{id}/image - GET
    if ($segments[0] === 'instance' && $method === 'GET' && $len === 4 && $segments[1] === 'connect' && $segments[3] === 'image') {
        $targetInstanceId = $segments[2];
        $targetInstance = findInstanceByUser($pdo, $userId, $targetInstanceId);
        if (!$targetInstance) sendError(404, 'Instância não encontrada.');
        $apiRes = callMotorRaw('GET', '/instance/connect/' . $targetInstance['instance_name'] . '/image');
        if ($apiRes['code'] >= 200 && $apiRes['code'] < 300 && $apiRes['body'] !== null) {
            header('Content-Type: ' . ($apiRes['content_type'] ?? 'image/png'));
            echo $apiRes['body'];
            exit;
        }
        sendError($apiRes['code'] >= 200 && $apiRes['code'] < 300 ? 200 : $apiRes['code'], 'Falha ao obter QR image', $apiRes);
    }

    // /instance/connectionState/{id} - GET
    if ($segments[0] === 'instance' && $method === 'GET' && $len === 3 && $segments[1] === 'connectionState') {
        $targetInstanceId = $segments[2];
        $targetInstance = findInstanceByUser($pdo, $userId, $targetInstanceId);
        if (!$targetInstance) sendError(404, 'Instância não encontrada.');
        $apiRes = callMotor('GET', '/instance/' . $targetInstance['instance_name'] . '/status');
        sendResponse($apiRes['code'] >= 200 && $apiRes['code'] < 300 ? 200 : $apiRes['code'], $apiRes['body']);
    }

    // /instance/logout/{id} - DELETE
    if ($segments[0] === 'instance' && $method === 'DELETE' && $len === 3 && $segments[1] === 'logout') {
        $targetInstanceId = $segments[2];
        $targetInstance = findInstanceByUser($pdo, $userId, $targetInstanceId);
        if (!$targetInstance) sendError(404, 'Instância não encontrada.');

        $pdo->beginTransaction(); // Início da Transação
        $apiRes = callMotor('POST', '/instance/' . $targetInstance['instance_name'] . '/logout');
        if ($apiRes['code'] >= 200 && $apiRes['code'] < 300) {
            $pdo->prepare('UPDATE instances SET status = ? WHERE id = ?')->execute(['disconnected', $targetInstance['id']]);
            $pdo->commit();
        } else {
            $pdo->rollBack();
        }
        sendResponse($apiRes['code'] >= 200 && $apiRes['code'] < 300 ? 200 : $apiRes['code'], $apiRes['body']);
    }

    // Create instance (POST /instance or /instance/create)
    if ($segments[0] === 'instance' && $method === 'POST' && ($len === 1 || ($len === 2 && $segments[1] === 'create'))) {
        if (empty($input)) sendError(400, 'Payload inválido.');
        $displayName = $input['instanceName'] ?? $input['name'] ?? $input['id'] ?? null;
        if (!$displayName) sendError(422, 'Campo instanceName/name/id obrigatório.');

        $instanceName = 'user' . $userId . '_' . time();
        $payload = array_merge(['id' => $instanceName, 'instanceName' => $displayName, 'name' => $displayName], $input);

        $pdo->beginTransaction(); // Início da Transação
        $apiRes = callMotor('POST', '/instance', $payload);
        if ($apiRes['code'] >= 200 && $apiRes['code'] < 300) {
            $pdo->prepare('INSERT INTO instances (user_id, instance_name, display_name, status) VALUES (?, ?, ?, ?)')->execute([$userId, $instanceName, $displayName, 'disconnected']);
            $pdo->commit();
            sendResponse(201, array_merge(['id' => $instanceName], $apiRes['body'] ?? []));
        } else {
            $pdo->rollBack();
        }
        sendError($apiRes['code'] < 400 ? 400 : $apiRes['code'], 'Erro de criação de instância', $apiRes['body']);
    }

    // /instance/ID endpoints
    if ($segments[0] === 'instance' && $len >= 2) {
        $instanceId = $segments[1];
        $instance = findInstanceByUser($pdo, $userId, $instanceId);
        if ($instance) {
            // GET /instance/{id}/status
            if ($len === 3 && $segments[2] === 'status' && $method === 'GET') {
                $apiRes = callMotor('GET', '/instance/' . $instance['instance_name'] . '/status');
                sendResponse($apiRes['code'] >= 200 && $apiRes['code'] < 300 ? 200 : $apiRes['code'], $apiRes['body']);
            }

            // POST /instance/{id}/connect
            if ($len === 3 && $segments[2] === 'connect' && $method === 'POST') {
                $apiRes = callMotor('POST', '/instance/' . $instance['instance_name'] . '/connect');
                sendResponse($apiRes['code'] >= 200 && $apiRes['code'] < 300 ? 200 : $apiRes['code'], $apiRes['body']);
            }

            // POST /instance/{id}/logout
            if ($len === 3 && $segments[2] === 'logout' && $method === 'POST') {
                $pdo->beginTransaction();
                $apiRes = callMotor('POST', '/instance/' . $instance['instance_name'] . '/logout');
                if ($apiRes['code'] >= 200 && $apiRes['code'] < 300) {
                    $pdo->prepare('UPDATE instances SET status = ? WHERE id = ?')->execute(['disconnected', $instance['id']]);
                    $pdo->commit();
                } else { $pdo->rollBack(); }
                sendResponse($apiRes['code'] >= 200 && $apiRes['code'] < 300 ? 200 : $apiRes['code'], $apiRes['body']);
            }

            // DELETE /instance/{id}
            if ($len === 2 && $method === 'DELETE') {
                $pdo->beginTransaction();
                $apiRes = callMotor('DELETE', '/instance/' . $instance['instance_name']);
                if ($apiRes['code'] >= 200 && $apiRes['code'] < 300) {
                    $pdo->prepare('DELETE FROM instances WHERE id = ? AND user_id = ?')->execute([$instance['id'], $userId]);
                    $pdo->commit();
                } else { $pdo->rollBack(); }
                sendResponse($apiRes['code'] >= 200 && $apiRes['code'] < 300 ? 200 : $apiRes['code'], $apiRes['body']);
            }

            // PUT /instance/{id}/update
            if ($len === 3 && $segments[2] === 'update' && $method === 'PUT') {
                if (empty($input)) sendError(400, 'Payload inválido.');
                $pdo->beginTransaction();
                $apiRes = callMotor('PUT', '/instance/' . $instance['instance_name'], $input); 
                if ($apiRes['code'] >= 200 && $apiRes['code'] < 300) {
                    if (isset($input['webhook']['url'])) {
                        $pdo->prepare('UPDATE instances SET webhook_url = ?, webhook_base64 = ? WHERE id = ? AND user_id = ?')->execute([$input['webhook']['url'], isset($input['webhook']['base64']) ? (int)$input['webhook']['base64'] : 0, $instance['id'], $userId]);
                    }
                }
                $pdo->commit();
                sendResponse($apiRes['code'] >= 200 && $apiRes['code'] < 300 ? 200 : $apiRes['code'], $apiRes['body']);
            }

            // POST /instance/{id}/message/{type}
            if ($len === 4 && $segments[2] === 'message' && $method === 'POST') {
                $messageType = $segments[3];
                $payload = (isset($input['payload'])) ? $input['payload'] : $input;
                
                $pdo->beginTransaction();
                $apiRes = callMotor('POST', '/instance/' . $instance['instance_name'] . '/message/' . $messageType, $payload);
                if (isset($payload['number'])) {
                    $status = ($apiRes['code'] >= 200 && $apiRes['code'] < 300) ? 'Enviado' : 'Erro';
                    $pdo->prepare('INSERT INTO message_logs (instance_id, destination_phone, message_type, status) VALUES (?, ?, ?, ?)')->execute([$instance['id'], $payload['number'], $messageType, $status]);
                }
                $pdo->commit();
                sendResponse($apiRes['code'] >= 200 && $apiRes['code'] < 300 ? 200 : $apiRes['code'], $apiRes['body']);
            }
        }
    }

    // /message/sendText/{instance} etc (legacy Swagger path)
    if ($segments[0] === 'message' && $len === 3 && $method === 'POST') {
        $messageMapping = ['sendText'=>'text','sendAudio'=>'audio','sendDocument'=>'document','sendImage'=>'image','sendList'=>'list','sendButtons'=>'buttons','sendMedia'=>'media','sendReaction'=>'reaction','sendWhatsAppAudio'=>'audio'];
        $action = $segments[1];
        $instance = findInstanceByUser($pdo, $userId, $segments[2]);
        $type = $messageMapping[$action] ?? null;

        if ($instance && $type) {
            $payload = (isset($input['payload'])) ? $input['payload'] : $input;
            $pdo->beginTransaction();
            $apiRes = callMotor('POST', '/instance/' . $instance['instance_name'] . '/message/' . $type, $payload);
            if (isset($payload['number'])) {
                $status = ($apiRes['code'] >= 200 && $apiRes['code'] < 300) ? 'Enviado' : 'Erro';
                $pdo->prepare('INSERT INTO message_logs (instance_id, destination_phone, message_type, status) VALUES (?, ?, ?, ?)')->execute([$instance['id'], $payload['number'], $type, $status]);
            }
            $pdo->commit();
            sendResponse($apiRes['code'] >= 200 && $apiRes['code'] < 300 ? 200 : $apiRes['code'], $apiRes['body']);
        }
    }

    // Chat legacy compatibility
    if ($segments[0] === 'chat' && $len >= 2 && $method === 'POST') {
        $instId = $segments[2] ?? ($input['instance'] ?? null);
        $instance = findInstanceByUser($pdo, $userId, $instId);
        if ($instance) {
            $apiRes = callMotor('POST', '/chat/' . $segments[1] . '/' . $instance['instance_name'], $input);
            sendResponse($apiRes['code'] >= 200 && $apiRes['code'] < 300 ? 200 : $apiRes['code'], $apiRes['body']);
        }
    }

    // Fallback antigo baseado em action
    $action = $_GET['action'] ?? '';
    if ($action) {
        switch ($action) {
           case 'send_message':
                $instanceDisplayName = $input['instance'] ?? '';
                $type = $input['type'] ?? 'text';
                $payload = $input['payload'] ?? [];
    
                $stmtInst = $pdo->prepare('SELECT instance_name, id FROM instances WHERE user_id = ? AND display_name = ?');
                $stmtInst->execute([$userId, $instanceDisplayName]);
                $instData = $stmtInst->fetch();
                if (!$instData) throw new Exception('Instância não encontrada.');

                // --- LÓGICA DE VALIDAÇÃO DE EXTENSÃO E MIMETYPE ---
                $fileName = $payload['fileName'] ?? 'arquivo';
                $extensao = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                // Mapeamento de Mimetypes
                $mimetypes = [
                    'ics'  => 'text/calendar',
                    'pdf'  => 'application/pdf',
                    'png'  => 'image/png',
                    'jpg'  => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'mp3'  => 'audio/mpeg'
                ];

                // Se a extensão existir no mapa, usa ela. Senão, tenta o padrão.
                $finalMimetype = $mimetypes[$extensao] ?? ($type === 'image' ? 'image/png' : 'application/octet-stream');
                // --------------------------------------------------

                $bodyMotor = ($type === 'text') ? ['number' => $payload['number'] ?? null, 'text' => $payload['text'] ?? ''] : [
                    'number' => $payload['number'] ?? null, 
                    'mediatype' => $type, 
                    'mimetype' => $finalMimetype, // Usa a variável validada aqui
                    'caption' => $payload['caption'] ?? '', 
                    'media' => $payload['url'] ?? '', 
                    'url' => $payload['url'] ?? '', 
                    'fileName' => $fileName
                ];

                $pdo->beginTransaction();
                // Envio para o motor (Evolution API ou similar)
                $apiRes = callMotor('POST', '/instance/' . $instData['instance_name'] . '/message/' . $type, $bodyMotor);
    
                $status = ($apiRes['code'] >= 200 && $apiRes['code'] < 300) ? 'Enviado' : 'Erro';
    
                $pdo->prepare('INSERT INTO message_logs (instance_id, destination_phone, message_type, status) VALUES (?, ?, ?, ?)')->execute([$instData['id'], $payload['number'], $type, $status]);
                $pdo->commit();
    
                sendResponse(200, ['success' => $status === 'Enviado', 'data' => $apiRes['body']]);
                break;

            case 'check_numbers':
                $instanceDisplayName = $input['instance'] ?? '';
                $numbers = $input['numbers'] ?? [];

                if (empty($numbers)) {
                    throw new Exception('Lista de números vazia.');
                }

                // 1. Busca o nome real da instância no banco
                $stmtInst = $pdo->prepare('SELECT instance_name FROM instances WHERE user_id = ? AND display_name = ?');
                $stmtInst->execute([$userId, $instanceDisplayName]);
                $instData = $stmtInst->fetch();

                if (!$instData) {
                    throw new Exception("Instância '{$instanceDisplayName}' não encontrada.");
                }

                // 2. Limpa os números (remove caracteres não numéricos)
                // A nova API aceita um array, então vamos processar todos os enviados
                $cleanedNumbers = array_map(function($num) {
                    return preg_replace('/\D/', '', $num);
                }, (array)$numbers);

                // 3. Prepara o corpo da requisição (conforme seu exemplo do Postman)
                $body = [
                    "numbers" => $cleanedNumbers
                ];

                // 4. Nova chamada: Método POST e rota /v1/chat/whatsappNumbers/
                // Certifique-se que a função callMotor suporte o terceiro parâmetro (body)
                $apiRes = callMotor( 'POST', '/chat/whatsappNumbers/' . $instData['instance_name'], $body );

                // 5. Retorna a resposta
                sendResponse(
                    $apiRes['code'] >= 200 && $apiRes['code'] < 300 ? 200 : $apiRes['code'], 
                    $apiRes['body']
                );
                break;
        }
    }

    sendError(404, 'Rota não encontrada.');

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    sendError(500, 'Erro de banco de dados: ' . $e->getMessage());
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    sendError(400, $e->getMessage());
}