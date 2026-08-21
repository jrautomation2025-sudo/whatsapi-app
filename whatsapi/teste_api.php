<?php
// Força o PHP a mostrar todos os erros na tela
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Modo de Depuração Ativado</h2>";

try {
    require_once 'config.php';
    echo "✔️ Arquivo config.php carregado com sucesso.<br>";
    
    // Verifica se a constante foi definida corretamente
    if (!defined('MASTER_API_KEY')) {
        throw new Exception("A constante MASTER_API_KEY não foi definida no seu config.php!");
    }
    
    echo "✔️ URL Base: " . API_BASE_URL . "<br>";
    
    $url = API_BASE_URL . '/instance';
    $ch = curl_init($url);
    echo "✔️ cURL inicializado na memória.<br>";

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . MASTER_API_KEY
    ]);
    echo "✔️ Cabeçalhos configurados. Disparando requisição para a API...<hr>";

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "<h3 style='color: red;'>Falha na Conexão (Erro cURL)!</h3>";
        echo "<strong>Detalhe do Erro:</strong> " . curl_error($ch) . "<br>";
    } else {
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        echo "<h3 style='color: green;'>Requisição Finalizada</h3>";
        echo "<strong>Código HTTP:</strong> " . $httpCode . "<br>";
        echo "<strong>Resposta da API:</strong><br>";
        echo "<pre style='background: #f4f4f4; padding: 10px; border: 1px solid #ccc;'>" . htmlspecialchars($response) . "</pre>";
    }

    curl_close($ch);

} catch (Throwable $e) {
    // Captura qualquer erro fatal do PHP e joga na tela
    echo "<h3 style='color: red;'>⚠️ ERRO FATAL ENCONTRADO:</h3>";
    echo "<strong>Mensagem:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>No arquivo:</strong> " . $e->getFile() . " (Linha " . $e->getLine() . ")";
}
?>