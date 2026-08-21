<?php
// debug_webhook.php
$logFile = 'webhook_raw.log';
$input = file_get_contents('php://input');
$headers = getallheaders();

$debug = [
    'date' => date('Y-m-d H:i:s'),
    'headers' => $headers,
    'body' => json_decode($input, true) ?? $input
];

file_put_contents($logFile, json_encode($debug, JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);
echo "OK";