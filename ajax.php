<?php
// ajax.php - Versão Corrigida e Sincronizada
error_reporting(E_ALL);
ini_set('display_errors', 0); 
session_start();
require_once 'database.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

// Pega os cabeçalhos da requisição
$headers = [];
if (function_exists('getallheaders')) {
    $headers = array_change_key_case(getallheaders(), CASE_LOWER);
}
$headers['x-csrf-token'] = $headers['x-csrf-token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
$csrf_token = $headers['x-csrf-token'];

// Validação CSRF para métodos POST ou delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $action === 'delete_instance') {
    if ($csrf_token !== ($_SESSION['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['error' => 'Erro de Segurança (CSRF Token Inválido)', 'csrf' => $csrf_token, 'session_csrf' => ($_SESSION['csrf_token'] ?? null)]);
        exit;
    }
}

// --- TRAVA DE SEGURANÇA POR EXPIRAÇÃO ---
$stmtExp = $pdo->prepare("SELECT expira_em FROM users WHERE id = ?");
$stmtExp->execute([$user_id]);
$user_check = $stmtExp->fetch();
$data_exp = strtotime($user_check['expira_em']);
$agora = time();

$acoes_livres = ['list_instances', 'get_logs', 'check_status'];
if ($agora > $data_exp && !in_array($action, $acoes_livres)) {
    http_response_code(403);
    echo json_encode([
        'error' => 'Assinatura Expirada! Por favor, realize a renovação para continuar.',
        'expired' => true
    ]);
    exit;
}

// Função oficial de comunicação com o motor
function callApi($method, $endpoint, $data = null) {
    // API_BASE_URL e MASTER_API_KEY devem estar no seu database.php ou config.php
    $url = API_BASE_URL . $endpoint;
    $ch = curl_init($url);
    
    $headers = [
        'apikey: ' . MASTER_API_KEY,
        'Content-Type: application/json'
    ];

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new Exception("Falha de rede (cURL): " . $err);
    }
    
    curl_close($ch);
    return ['code' => $httpCode, 'body' => json_decode($response, true) ?? $response];
}

function callApiRaw($method, $endpoint, $data = null) {
    $url = API_BASE_URL . $endpoint;
    $ch = curl_init($url);

    $headers = [
        'apikey: ' . MASTER_API_KEY,
        'Content-Type: application/json'
    ];

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

    if (curl_errno($ch)) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new Exception("Falha de rede (cURL): " . $err);
    }

    curl_close($ch);
    return ['code' => $httpCode, 'body' => $response, 'content_type' => $contentType];
}

try {
    switch ($action) {
        
        case 'list_instances':
            $stmt = $pdo->prepare("SELECT * FROM instances WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $instances = $stmt->fetchAll();
            
            foreach ($instances as &$inst) {
                try {
                    $apiRes = callApi('GET', '/instance/' . $inst['instance_name'] . '/status');
                    $apiStatus = ($apiRes['code'] >= 200 && $apiRes['code'] < 300) ? ($apiRes['body']['state'] ?? $apiRes['body']['status'] ?? 'disconnected') : 'error';
                    $inst['api_status'] = $apiStatus;
                    
                    if ($apiStatus !== 'error' && $inst['status'] !== $apiStatus) {
                        $upd = $pdo->prepare("UPDATE instances SET status = ? WHERE id = ?");
                        $upd->execute([$apiStatus, $inst['id']]);
                    }
                } catch (Exception $e) { $inst['api_status'] = 'error'; }
            }
            echo json_encode($instances);
            break;

        case 'check_status':
            $instanceName = $_GET['id'];
            $apiRes = callApi('GET', '/instance/' . $instanceName . '/status');
            $status = ($apiRes['code'] >= 200 && $apiRes['code'] < 300) ? ($apiRes['body']['state'] ?? $apiRes['body']['status'] ?? 'disconnected') : 'error';
            echo json_encode(['status' => $status]);
            break;

        case 'save_webhook':
            $data = json_decode(file_get_contents('php://input'), true);
            $instance = $data['id'] ?? '';
            $url = $data['webhook_url'] ?? '';
            $isBase64 = isset($data['base64']) ? (bool)$data['base64'] : false;

            // 1. Atualiza banco local (Adicione a coluna webhook_base64 se não existir)
            $stmt = $pdo->prepare("UPDATE instances SET webhook_url = ?, webhook_base64 = ? WHERE instance_name = ?");
            $stmt->execute([$url, ($isBase64 ? 1 : 0), $instance]);

            // 2. Envia para a API WhatsMiau
            $payload = [
                "webhook" => [
                    "enabled" => true,
                    "url" => $url,
                    "base64" => $isBase64,
                    "events" => ["MESSAGES_UPSERT"] // Você pode expandir esse array se quiser
                ]
            ];

            $res = callApi('PUT', "/instance/update/{$instance}", $payload);

            echo json_encode(['success' => true, 'api_raw' => $res]);
            break;

        case 'create_instance':
            $data = json_decode(file_get_contents('php://input'), true);
            $displayName = preg_replace('/[^a-zA-Z0-9_ -]/s', '', $data['name']);
            //$instanceName = 'user' . $user_id . '_' . time();
            $alwaysOnline = true;
            $groupsIgnore = true;
            $instanceName = $displayName;

            $apiRes = callApi('POST', '/instance', ['id' => $instanceName, 'instanceName' => $displayName, 'name' => $displayName, 'alwaysOnline' => $alwaysOnline, 'groupsIgnore' => $groupsIgnore]);
            
            if ($apiRes['code'] >= 200 && $apiRes['code'] < 300) {
                $stmt = $pdo->prepare("INSERT INTO instances (user_id, instance_name, display_name) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $instanceName, $displayName]);
                echo json_encode(['success' => true]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Erro da API: ' . json_encode($apiRes['body'])]);
            }
            break;

        case 'delete_instance':
            $instanceName = $_GET['id'];
            callApi('DELETE', '/instance/' . $instanceName);
            $stmt = $pdo->prepare("DELETE FROM instances WHERE user_id = ? AND instance_name = ?");
            $stmt->execute([$user_id, $instanceName]);
            echo json_encode(['success' => true]);
            break;

        case 'connect_instance':
            $instanceName = $_GET['id'];
            $apiRes = callApi('POST', '/instance/' . $instanceName . '/connect');

            if (!empty($apiRes['body']['base64']) || !empty($apiRes['body']['qrcode']) || !empty($apiRes['body']['qr'])) {
                echo json_encode($apiRes['body']);
                break;
            }

            // Tenta rota alternativa de conexão do Swagger
            $apiRes = callApi('GET', '/instance/connect/' . $instanceName);
            if (!empty($apiRes['body']['base64']) || !empty($apiRes['body']['qrcode']) || !empty($apiRes['body']['qr'])) {
                echo json_encode($apiRes['body']);
                break;
            }

            // Tenta rota de imagem /instance/connect/{id}/image
            $apiRaw = callApiRaw('GET', '/instance/connect/' . $instanceName . '/image');
            if ($apiRaw['code'] >= 200 && $apiRaw['code'] < 300 && !empty($apiRaw['body'])) {
                $base64 = base64_encode($apiRaw['body']);
                echo json_encode(['base64' => $base64, 'content_type' => $apiRaw['content_type']]);
                break;
            }

            // fallback
            echo json_encode($apiRes['body'] ?: ['error' => 'Falha ao conectar instância']);
            break;

        case 'logout_instance':
            $instanceName = $_GET['id'];
            callApi('POST', '/instance/' . $instanceName . '/logout');
            $stmt = $pdo->prepare("UPDATE instances SET status = 'disconnected' WHERE user_id = ? AND instance_name = ?");
            $stmt->execute([$user_id, $instanceName]);
            echo json_encode(['success' => true]);
            break;

        case 'send_message':
            $data = json_decode(file_get_contents('php://input'), true);
            $instanceName = $data['instance'];
            $type = $data['type']; 
            $payload = $data['payload'];
            
            $stmt = $pdo->prepare("SELECT id FROM instances WHERE user_id = ? AND instance_name = ?");
            $stmt->execute([$user_id, $instanceName]);
            $instanceData = $stmt->fetch();
            if (!$instanceData) throw new Exception("Instância não encontrada.");

            $endpoint = "/instance/{$instanceName}/message/{$type}"; 
            $apiRes = callApi('POST', $endpoint, $payload);
            
            $status = ($apiRes['code'] >= 200 && $apiRes['code'] < 300) ? 'Enviado' : 'Erro';
            $stmtLog = $pdo->prepare("INSERT INTO message_logs (instance_id, destination_phone, message_type, status) VALUES (?, ?, ?, ?)");
            $stmtLog->execute([$instanceData['id'], $payload['number'], $type, $status]);

            echo json_encode(['success' => ($status === 'Enviado'), 'api' => $apiRes['body']]);
            break;

        case 'get_logs':
            $stmt = $pdo->prepare("
                SELECT ml.destination_phone, ml.message_type, ml.status, ml.sent_at, i.display_name 
                FROM message_logs ml 
                JOIN instances i ON ml.instance_id = i.id 
                WHERE i.user_id = ? 
                ORDER BY ml.sent_at DESC LIMIT 10
            ");
            $stmt->execute([$user_id]);
            echo json_encode($stmt->fetchAll());
            break;

        case 'check_numbers':
            $data = json_decode(file_get_contents('php://input'), true);
            $instanceName = $data['instance'];
            $number = $data['numbers'][0];
            $res = callApi('GET', "/instance/{$instanceName}/contact/check/{$number}");
            echo json_encode(['code' => $res['code'], 'body' => $res['body']]);
            break;
            
        case 'get_dashboard_stats':
            // Pega os últimos 7 dias
            $stmt = $pdo->prepare("
            SELECT 
               DATE(sent_at) as data, 
               COUNT(*) as total,
               SUM(CASE WHEN status = 'Enviado' THEN 1 ELSE 0 END) as sucessos,
               SUM(CASE WHEN status = 'Erro' THEN 1 ELSE 0 END) as erros
            FROM message_logs 
            WHERE sent_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(sent_at)
            ORDER BY data ASC
            ");
            $stmt->execute();
            echo json_encode($stmt->fetchAll());
            break;

        default:
            http_response_code(404);
            echo json_encode(['error' => 'Ação desconhecida']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage(), 'line' => $e->getLine()]);
}