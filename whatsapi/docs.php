<?php
// docs.php - Versão Final de Alto Contraste com Sidebar e Postman
require_once 'database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /whatsapi/');
    exit;
}

$stmt = $pdo->prepare("SELECT api_token FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$apiKey = $user['api_token'] ?? 'SEU_TOKEN_AQUI';

include 'sidebar.php'; 
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Documentação API | JR Tech Automation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/svg+xml" href="/whatsapi/jrtec.svg">
    <style>
        :root {
            --bg-main: #0a0b0d;
            --card-bg: #14171c;
            --accent: #00ff88;
            --border: #23272e;
            --text-pure: #ffffff;
            --text-label: #ced4da;
        }

        body { background-color: var(--bg-main); color: var(--text-pure); font-family: 'Inter', sans-serif; scroll-behavior: smooth; }
        .main-wrapper { margin-left: 260px; padding: 40px; min-height: 100vh; }
        .doc-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; padding: 25px; margin-bottom: 30px; }
        
        .method { font-weight: 800; padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; margin-right: 12px; text-transform: uppercase; }
        .method-post { background: var(--accent); color: #000; }
        
        .code-block { background: #010409; border: 1px solid var(--border); border-radius: 8px; padding: 20px; }
        .token-highlight { color: var(--accent); font-weight: bold; }
        
        pre { margin-bottom: 0; color: #79c0ff; font-size: 0.85rem; line-height: 1.5; font-family: 'Fira Code', monospace; }
        .table-docs { font-size: 0.85rem; color: var(--text-label); }
        .table-docs thead th { color: var(--text-pure); border-bottom: 1px solid var(--border); text-transform: uppercase; font-size: 0.75rem; }
        
        .text-accent { color: var(--accent) !important; }
        hr { border-color: var(--border); opacity: 1; margin: 40px 0; }
        code { background: rgba(0, 255, 136, 0.1); color: var(--accent); padding: 2px 6px; border-radius: 4px; }
        .btn-purple { background: #a371f7; color: white; font-weight: bold; border: none; }
        .btn-purple:hover { background: #8e5ce6; color: white; }
    </style>
</head>
<body>

<div class="main-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-0">Documentação da API</h2>
            <p class="text-white">Integração técnica para desenvolvedores.</p>
        </div>
    </div>

    <section id="intro">
        <div class="doc-card">
            <h4 class="fw-bold mb-3"><i class="fas fa-rocket text-accent me-2"></i>Introdução</h4>
            <p class="text-secondary">Utilize o endpoint abaixo para todas as requisições externas:</p>
            <div class="alert bg-dark border-secondary mt-3">
                <code class="fs-5">https://jrtec.com.br/whatsapi/external</code>
            </div>

            <div class="mt-4 p-4 border border-secondary rounded" style="background: rgba(163, 113, 247, 0.05);">
                <h5 class="text-white"><i class="fab fa-wpforms me-2" style="color: #a371f7;"></i>Postman Collection</h5>
                <p class="text-white small">Baixe nossa coleção oficial para testar os envios em menos de 1 minuto.</p>
                <a href="JR Tech Automation API.postman_collection.json" download class="btn btn-sm btn-purple">
                    <i class="fas fa-download me-2"></i>Download JSON Collection
                </a>
                <a href="Personal Assistant JR Tech Automation.json" download class="btn btn-sm btn-purple">
                    <i class="fas fa-download me-2"></i>Download Agente Atendimento n8n
                </a>
            </div>
        </div>
    </section>

    <hr>

    <section id="auth">
    <div class="doc-card">
        <h4 class="fw-bold mb-3"><i class="fas fa-lock text-accent me-2"></i>Autenticação</h4>
        <p class="text-white mb-4">Para autenticar suas chamadas, envie seu token privado no cabeçalho <strong>(Header)</strong> de cada requisição.</p>
        
        <div class="input-group input-group-lg mb-3">
            <span class="input-group-text bg-dark border-secondary text-white fw-bold" style="font-size: 0.9rem; min-width: 100px; justify-content: center;">apikey:</span>
            
            <input type="password" id="apiTokenDocs" class="form-control border-secondary text-accent fw-bold bg-dark" 
                   value="<?php echo $apiKey; ?>" readonly 
                   style="letter-spacing: 2px; border-right: none; font-size: 1rem;">
            
            <button class="btn btn-outline-secondary border-secondary bg-dark" type="button" onclick="toggleTokenDocs()" style="border-left: none; border-right: none;">
                <i id="toggleIconDocs" class="bi bi-eye-slash text-white"></i>
            </button>
            
            <button class="btn btn-outline-info border-secondary bg-dark px-4" type="button" onclick="copyTokenDocs(this)">
                <i class="bi bi-clipboard text-white"></i>
            </button>
        </div>
        
        <div class="d-flex align-items-center mt-2">
            <i class="bi bi-info-circle text-white me-2" style="font-size: 0.9rem; opacity: 0.7;"></i>
            <span class="text-white small" style="opacity: 0.7;">Este token é pessoal e não deve ser compartilhado com terceiros.</span>
        </div>
        
        <div class="mt-3">
            <p class="text-secondary small mb-2">Exemplo de Headers:</p>
            <div class="code-block" style="padding: 10px 20px;">
            <pre style="margin:0;">
Content-Type: application/json
apikey: <span class="token-highlight">Informe aqui sua chave secreta de api</span></pre>
            </div>
        </div>
    </div>
    </section>

    <hr>

    <section id="send-text">
        <div class="doc-card">
            <h4 class="fw-bold mb-4"><span class="method method-post">POST</span> Enviar Texto</h4>
            <div class="row">
                <div class="col-lg-6">
                    <table class="table table-dark table-docs border-secondary">
                        <thead><tr><th>Campo</th><th>Tipo</th><th>Descrição</th></tr></thead>
                        <tbody>
                            <tr><td>instance</td><td>string</td><td>Nome amigável da instância</td></tr>
                            <tr><td>type</td><td>string</td><td><code>text</code></td></tr>
                            <tr><td>payload[number]</td><td>string</td><td>DDI+DDD+Número</td></tr>
                            <tr><td>payload[text]</td><td>string</td><td>Conteúdo da mensagem</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-lg-6">
                    <div class="code-block"><pre>{
  "instance": "SuaInstancia",
  "type": "text",
  "payload": {
    "number": "5581984201425",
    "text": "Olá! Teste de API JR Tech."
  }
}</pre></div>
                </div>
            </div>
        </div>
    </section>

    <hr>

    <section id="send-image">
        <div class="doc-card">
            <h4 class="fw-bold mb-4"><span class="method method-post">POST</span> Enviar Imagem</h4>
            <div class="row">
                <div class="col-lg-6">
                    <table class="table table-dark table-docs border-secondary">
                        <thead><tr><th>Campo</th><th>Tipo</th><th>Descrição</th></tr></thead>
                        <tbody>
                            <tr><td>type</td><td>string</td><td><code>image</code></td></tr>
                            <tr><td>payload[url]</td><td>string</td><td>Link público da imagem</td></tr>
                            <tr><td>payload[caption]</td><td>string</td><td>Legenda opcional</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-lg-6">
                    <div class="code-block"><pre>{
  "instance": "SuaInstancia",
  "type": "image",
  "payload": {
    "number": "5581984201425",
    "url": "https://site.com/foto.png",
    "caption": "Foto enviada via API"
  }
}</pre></div>
                </div>
            </div>
        </div>
    </section>

    <hr>

    <section id="send-audio">
        <div class="doc-card">
            <h4 class="fw-bold mb-4"><span class="method method-post">POST</span> Enviar Áudio</h4>
            <div class="row">
                <div class="col-lg-6">
                    <p class="text-secondary small">O áudio é enviado como uma mensagem de voz.</p>
                    <table class="table table-dark table-docs border-secondary">
                        <thead><tr><th>Campo</th><th>Tipo</th><th>Descrição</th></tr></thead>
                        <tbody>
                            <tr><td>type</td><td>string</td><td><code>audio</code></td></tr>
                            <tr><td>payload[url]</td><td>string</td><td>Link do MP3 ou OGG</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-lg-6">
                    <div class="code-block"><pre>{
  "instance": "SuaInstancia",
  "type": "audio",
  "payload": {
    "number": "5581984201425",
    "url": "https://site.com/audio.mp3"
  }
}</pre></div>
                </div>
            </div>
        </div>
    </section>

    <hr>

    <section id="send-doc">
        <div class="doc-card">
            <h4 class="fw-bold mb-4"><span class="method method-post">POST</span> Enviar Documento</h4>
            <div class="row">
                <div class="col-lg-6">
                    <table class="table table-dark table-docs border-secondary">
                        <thead><tr><th>Campo</th><th>Tipo</th><th>Descrição</th></tr></thead>
                        <tbody>
                            <tr><td>type</td><td>string</td><td><code>document</code></td></tr>
                            <tr><td>payload[url]</td><td>string</td><td>Link do PDF/DOCX</td></tr>
                            <tr><td>payload[fileName]</td><td>string</td><td>Nome do arquivo</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-lg-6">
                    <div class="code-block"><pre>{
  "instance": "SuaInstancia",
  "type": "document",
  "payload": {
    "number": "5581984201425",
    "url": "https://site.com/arquivo.pdf",
    "fileName": "fatura_cliente.pdf",
    "mimetype": "application/pdf"
  }
}</pre></div>
                </div>
            </div>
        </div>
    </section>

    <hr>

    <section id="check-number" class="mb-5">
        <div class="doc-card border-accent" style="border-style: dashed;">
            <h4 class="fw-bold mb-4"><span class="method method-post">POST</span> Validar WhatsApp</h4>
            <div class="row">
                <div class="col-lg-6">
                    <table class="table table-dark table-docs border-secondary">
                        <thead><tr><th>Campo</th><th>Tipo</th><th>Descrição</th></tr></thead>
                        <tbody>
                            <tr><td>instance</td><td>string</td><td>Nome da instância</td></tr>
                            <tr><td>numbers</td><td>array</td><td>Lista de números</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-lg-6">
                    <div class="code-block"><pre>{
  "instance": "SuaInstancia",
  "numbers": ["5581984201425"]
}</pre></div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function toggleTokenDocs() {
    const input = document.getElementById('apiTokenDocs');
    const icon = document.getElementById('toggleIconDocs');
    if (input.type === "password") {
        input.type = "text";
        input.style.letterSpacing = "normal";
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    } else {
        input.type = "password";
        input.style.letterSpacing = "2px";
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    }
}

function copyTokenDocs(btn) {
    const input = document.getElementById('apiTokenDocs');
    input.select();
    navigator.clipboard.writeText(input.value).then(() => {
        const icon = btn.querySelector('i');
        icon.classList.replace('bi-clipboard', 'bi-check-lg');
        icon.classList.add('text-accent');
        setTimeout(() => {
            icon.classList.replace('bi-check-lg', 'bi-clipboard');
            icon.classList.remove('text-accent');
        }, 2000);
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
