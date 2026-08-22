<?php
// painel.php - Versão Final de Alto Contraste (SaaS Evolution Style)
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();
require_once 'database.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 1. Busca dados atualizados do usuário
$stmt = $pdo->prepare("SELECT plano, expira_em, api_token FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_data = $stmt->fetch();

// 2. Lógica de cálculo de expiração
$hoje = new DateTime();
$expiracao = new DateTime($user_data['expira_em']);
$dias_restantes = ($hoje > $expiracao) ? 0 : (int)$hoje->diff($expiracao)->format("%a");
$status_class = ($dias_restantes <= 2) ? "text-danger" : "text-accent";

// 3. Busca métricas para os cards
$stmtCount = $pdo->prepare("SELECT COUNT(*) as total FROM instances WHERE user_id = ?");
$stmtCount->execute([$_SESSION['user_id']]);
$total_instancias = $stmtCount->fetch()['total'] ?? 0;

$stmtCount = $pdo->prepare("SELECT COUNT(*) as total FROM instances WHERE user_id = ?");
$stmtCount->execute([$_SESSION['user_id']]);
// Aqui $instancias passa a ser um número (ex: 0, 1, 2...)
$instancias = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

include 'sidebar.php'; 
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - JR Tech Automation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/svg+xml" href="jrtec.svg">
    <style>
        :root {
            --bg-main: #0a0b0d;
            --card-bg: #14171c;
            --accent: #00ff88;
            --border: #23272e;
            --text-pure: #ffffff; /* Branco total para leitura */
            --text-label: #ced4da; /* Cinza claro para labels */
            --text-dim: #9ba3af;
        }

        body { background: var(--bg-main); color: var(--text-pure); font-family: 'Inter', sans-serif; }
        .main-wrapper { margin-left: 260px; padding: 40px; min-height: 100vh; }
        
        /* Cards com Contraste Elevado */
        .doc-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; padding: 25px; margin-bottom: 25px; }
        
        /* Tipografia Corrigida */
        h2, h4, h5, h6, .fw-bold, .card-title { color: var(--text-pure) !important; }
        .text-muted { color: var(--text-dim) !important; }
        .small-label { color: var(--text-label); font-size: 0.75rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; }

        /* Abas e Navegação */
        .nav-tabs { border-bottom: 1px solid var(--border); }
        .nav-link { color: var(--text-dim) !important; border: none !important; font-weight: 500; }
        .nav-link.active { color: var(--accent) !important; background: transparent !important; border-bottom: 2px solid var(--accent) !important; font-weight: 700; }
        
        /* Badges de Status Glow */
        .status-pill { font-size: 0.7rem; padding: 3px 12px; border-radius: 20px; border: 1px solid; font-weight: 800; text-transform: uppercase; }
        .status-online { background: rgba(0, 255, 136, 0.1); color: var(--accent); border-color: var(--accent); box-shadow: 0 0 10px rgba(0, 255, 136, 0.1); }
        .status-offline { background: rgba(255, 77, 77, 0.1); color: #ff4d4d; border-color: #ff4d4d; }

        /* Inputs e Selects de Alta Visibilidade */
        input, textarea, select { 
            background-color: #0d1117 !important; 
            border: 1px solid var(--border) !important; 
            color: #ffffff !important; 
            font-weight: 500;
        }
        input::placeholder { color: #57606a !important; }
        
        .btn-accent { background: var(--accent); color: #000; font-weight: 800; border: none; text-transform: uppercase; }
        .btn-accent:hover { background: #00e67a; color: #000; transform: translateY(-1px); }

        .text-accent { color: var(--accent) !important; }
        .text-info { color: #5cc2ff !important; }

        .table-dark { --bs-table-bg: transparent; color: var(--text-label); }
        .table thead th { color: var(--text-pure); border-bottom: 1px solid var(--border); font-size: 0.7rem; text-transform: uppercase; }
        
        .qr-container { background: #fff; padding: 15px; border-radius: 8px; display: inline-block; }
    </style>
</head>
<body>

<div class="main-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold">Dashboard Geral</h2>
            <p class="text-muted small">Controle total de instâncias e disparos.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-dark border border-secondary p-2" style="font-size: 0.8rem; color: #fff;">PLANO: <span class="text-accent"><?= strtoupper($user_data['plano']) ?></span></span>
            <div class="<?= $status_class ?> small mt-2 fw-bold"><?= ($dias_restantes > 0) ? "• $dias_restantes dias restantes" : "• Assinatura Expirada" ?></div>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-md-4">
            <div class="doc-card d-flex align-items-center">
                <div class="p-3 rounded bg-dark border border-secondary me-3"><i class="bi bi-phone text-accent h4 mb-0"></i></div>
                <div>
                    <span class="small-label d-block">Minhas Instâncias</span>
                    <h4 class="mb-0 fw-bold"><?= $total_instancias ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-8">
        <div class="doc-card d-flex align-items-center">
        <div class="p-3 rounded bg-dark border border-secondary me-3">
            <i class="bi bi-key text-info h4 mb-0"></i>
        </div>
        <div class="w-100">
            <span class="small-label d-block mb-1">Token de API Privado</span>
            <div class="input-group">
                <input type="password" id="apiTokenInput" class="form-control border-secondary text-info fw-bold bg-dark" 
                       value="<?= $user_data['api_token'] ?>" readonly 
                       style="letter-spacing: 2px; border-right: none; font-size: 0.85rem;">
                
                <button class="btn btn-outline-secondary border-secondary" type="button" onclick="toggleToken()" style="border-left: none; border-right: none;">
                    <i id="toggleIcon" class="bi bi-eye-slash"></i>
                </button>
                
                <button class="btn btn-outline-info border-secondary" type="button" onclick="copyToken()">
                    <i class="bi bi-clipboard"></i> Copiar
                </button>
            </div>
        </div>
        </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="doc-card">
                <h6 class="fw-bold mb-4"><i class="bi bi-plus-circle me-2 text-accent"></i>Nova Instância</h6>
                <form id="createInstanceForm" class="mb-4">
                    <div class="input-group input-group-sm">
                        <input type="text" id="instanceName" class="form-control" placeholder="Nome da instância..." required>
                        <?php if ($instancias >= 3): ?>
                        <button class="btn btn-accent px-3" disabled type="submit">Criar</button>
                        <?php else: ?>
                        <button class="btn btn-accent px-3" type="submit">Criar</button>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="small-label">Suas Conexões</span>
                    <button class="btn btn-sm text-accent p-0" onclick="loadInstances()"><i class="bi bi-arrow-clockwise h5"></i></button>
                </div>

                <div id="instanceList" class="d-grid gap-3">
                    <div class="text-center py-4"><div class="spinner-border spinner-border-sm text-accent"></div></div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="doc-card">
                <ul class="nav nav-tabs mb-4" id="msgTabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-text"><i class="bi bi-chat-left-dots me-2"></i>Texto</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-image"><i class="bi bi-image me-2"></i>Imagem</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-doc"><i class="bi bi-file-earmark-pdf"></i>Documento</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-audio"><i class="bi bi-mic"></i>Áudio</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-tools"><i class="bi bi-tools"></i>Ferramentas</button></li>
                </ul>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="small-label mb-2 d-block text-accent">Instância Remetente</label>
                        <select class="form-select form-select-sm" id="msgInstanceId"><option value="">Carregando...</option></select>
                    </div>
                    <div class="col-md-6">
                        <label class="small-label mb-2 d-block text-info">WhatsApp de Destino</label>
                        <input type="text" class="form-control form-select-sm" id="msgPhone" placeholder="Ex: 5581984201425">
                    </div>
                </div>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tab-text">
                        <textarea class="form-control mb-3" id="payload-text" rows="4" placeholder="Sua mensagem aqui..."></textarea>
                        <button class="btn btn-accent w-100 py-2" onclick="sendMessage('text')">Enviar Mensagem</button>
                    </div>
                    <div class="tab-pane fade" id="tab-image">
                        <input type="url" class="form-control mb-2" id="payload-image-url" placeholder="URL da Imagem (JPG/PNG)">
                        <input type="text" class="form-control mb-3" id="payload-image-caption" placeholder="Legenda Opcional">
                        <button class="btn btn-accent w-100 py-2" onclick="sendMessage('image')">Enviar Imagem</button>
                    </div>
                    <div class="tab-pane fade" id="tab-doc">
                        <input type="url" class="form-control mb-2" id="payload-doc-url" placeholder="URL do Arquivo (PDF/DOCX)">
                        <input type="text" class="form-control mb-3" id="payload-doc-name" placeholder="nome_do_arquivo.pdf">
                        <button class="btn btn-accent w-100 py-2" onclick="sendMessage('document')">Enviar Documento</button>
                    </div>
                    <div class="tab-pane fade" id="tab-audio">
                        <input type="url" class="form-control mb-3" id="payload-audio-url" placeholder="URL do Áudio (.mp3, .ogg)">
                        <button class="btn btn-accent w-100 py-2" onclick="sendMessage('audio')">Enviar Áudio</button>
                    </div>
                    <div class="tab-pane fade" id="tab-tools">
                        <div class="row g-3">
                            <div class="col-md-6 border-end border-secondary">
                                <span class="small-label mb-2 d-block">Validação de Número</span>
                                <button class="btn btn-sm btn-outline-info w-100" onclick="checkWhatsApp()"><i class="bi bi-search me-2"></i>Verificar Existência</button>
                                <div id="checkResult" class="small mt-2 fw-bold"></div>
                            </div>
                            <div class="col-md-6">
                                <span class="small-label mb-2 d-block">Simulação de Chat</span>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-xs btn-outline-warning py-1 flex-grow-1" onclick="setPresence('composing')">Digitando</button>
                                    <button class="btn btn-xs btn-outline-danger py-1 flex-grow-1" onclick="setPresence('recording')">Gravando</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="msgResult" class="mt-4"></div>
            </div>

            <div class="doc-card">
                <h6 class="fw-bold mb-4"><i class="bi bi-clock-history me-2 text-accent"></i>Histórico Recente</h6>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle">
                        <thead><tr><th>Data</th><th>Instância</th><th>Destino</th><th>Status</th></tr></thead>
                        <tbody id="logTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="qrModal" data-bs-backdrop="static" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content bg-dark border-secondary"><div class="modal-header border-secondary"><h6 class="modal-title fw-bold">Conectar WhatsApp</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body text-center py-4" id="qrModalBody"></div></div></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const CSRF_TOKEN = "<?= $_SESSION['csrf_token'] ?>";
    let currentQrModal = null;
    let statusPollingInterval = null;

    document.addEventListener("DOMContentLoaded", () => {
        currentQrModal = new bootstrap.Modal(document.getElementById('qrModal'));
        document.getElementById('qrModal').addEventListener('hidden.bs.modal', () => { if (statusPollingInterval) clearInterval(statusPollingInterval); });
        loadInstances();
        loadLogs();
    });

    async function loadInstances() {
        const list = document.getElementById('instanceList');
        const select = document.getElementById('msgInstanceId');
        try {
            const res = await fetch('ajax.php?action=list_instances');
            const data = await res.json();
            list.innerHTML = ''; select.innerHTML = '<option value="">Selecione...</option>';
            data.forEach(inst => {
                const isConnected = (inst.api_status === 'open' || inst.api_status === 'connected');
                list.innerHTML += `
                    <div class="p-3 border border-secondary rounded" style="background: rgba(255,255,255,0.02)">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div><div class="fw-bold small" style="color:#fff">${inst.display_name}</div><div class="text-muted small">${inst.instance_name}</div></div>
                            <span class="status-pill ${isConnected ? 'status-online' : 'status-offline'}">${isConnected ? 'Online' : 'Offline'}</span>
                        </div>
                        <div class="d-flex gap-2 mb-3">
                            ${isConnected ? `<button class="btn btn-xs btn-outline-warning w-100 py-1" style="font-size:0.7rem" onclick="logoutInstance('${inst.instance_name}')">LOGOUT</button>` : `<button class="btn btn-xs btn-accent w-100 py-1" style="font-size:0.7rem" onclick="connectInstance('${inst.instance_name}')">CONECTAR</button>`}
                            <button class="btn btn-xs btn-outline-danger py-1" onclick="deleteInstance('${inst.instance_name}')"><i class="bi bi-trash"></i></button>
                        </div>
                        <div class="input-group input-group-sm">
                            <input type="url" class="form-control" id="webhook-${inst.instance_name}" placeholder="URL Webhook" value="${inst.webhook_url || ''}">
                            <button class="btn btn-outline-info" onclick="saveWebhook('${inst.instance_name}')">Ok</button>
                        </div>
                    </div>`;
                if(isConnected) select.innerHTML += `<option value="${inst.instance_name}">${inst.display_name}</option>`;
            });
        } catch (e) { list.innerHTML = 'Erro ao carregar.'; }
    }

    async function connectInstance(id) {
        const body = document.getElementById('qrModalBody');
        body.innerHTML = '<div class="spinner-border text-accent mb-3"></div><p>Gerando QR Code...</p>';
        currentQrModal.show();
        try {
            const res = await fetch(`ajax.php?action=connect_instance&id=${id}`);
            const data = await res.json();
            const qr = data.base64 || data.code || data.qrcode;
            if (qr) {
                body.innerHTML = `<div class="qr-container"><img src="${qr}" class="img-fluid"></div><p class="mt-3 text-accent small fw-bold"><span class="spinner-grow spinner-grow-sm"></span> Aguardando leitura...</p>`;
                if (statusPollingInterval) clearInterval(statusPollingInterval);
                statusPollingInterval = setInterval(() => checkConnectionStatus(id), 3000);
            } else { body.innerHTML = '<div class="alert alert-success">Pronta!</div>'; setTimeout(() => { currentQrModal.hide(); loadInstances(); }, 2000); }
        } catch (e) { body.innerHTML = 'Erro QR.'; }
    }

    async function checkConnectionStatus(id) {
        try {
            const res = await fetch(`ajax.php?action=check_status&id=${id}`);
            const data = await res.json();
            if (data.status === 'open' || data.status === 'connected') {
                clearInterval(statusPollingInterval);
                document.getElementById('qrModalBody').innerHTML = '<div class="alert alert-success fw-bold">✓ Conectado com sucesso!</div>';
                setTimeout(() => { currentQrModal.hide(); loadInstances(); }, 1500);
            }
        } catch (e) {}
    }

    async function sendMessage(type) {
        const instance = document.getElementById('msgInstanceId').value;
        const phone = document.getElementById('msgPhone').value;
        const resDiv = document.getElementById('msgResult');
        if (!instance || !phone) return;
        let p = { number: phone };
        if (type === 'text') p.text = document.getElementById('payload-text').value;
        else if (type === 'image') { p.url = document.getElementById('payload-image-url').value; p.caption = document.getElementById('payload-image-caption').value; }
        else if (type === 'document') { p.url = document.getElementById('payload-doc-url').value; p.fileName = document.getElementById('payload-doc-name').value; }
        else if (type === 'audio') p.url = document.getElementById('payload-audio-url').value;
        
        // Super Payload Fallback
        p.media = p.url; p.link = p.url; p.image = p.url;
        
        resDiv.innerHTML = '<div class="text-accent small"><span class="spinner-border spinner-border-sm"></span> Enviando...</div>';
        try {
            const res = await fetch('ajax.php?action=send_message', {
                method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN},
                body: JSON.stringify({ instance, type, payload: p })
            });
            if (res.status === 403) { resDiv.innerHTML = '<div class="alert alert-danger">Plano Expirado!</div>'; return; }
            resDiv.innerHTML = res.ok ? '<div class="text-accent fw-bold">✓ Mensagem Enviada!</div>' : '<div class="text-danger">✗ Erro no Envio.</div>';
            loadLogs();
        } catch (e) { resDiv.innerHTML = 'Erro API.'; }
    }

    async function loadLogs() {
        const tbody = document.getElementById('logTableBody');
        try {
            const res = await fetch('ajax.php?action=get_logs');
            const data = await res.json();
            tbody.innerHTML = '';
            data.forEach(log => {
                const date = new Date(log.sent_at).toLocaleString('pt-BR');
                const status = log.status === 'Enviado' ? '<span class="text-accent">✓</span>' : '<span class="text-danger">✗</span>';
                tbody.innerHTML += `<tr><td class="small text-muted">${date}</td><td class="fw-bold">${log.display_name}</td><td>${log.destination_phone}</td><td>${status}</td></tr>`;
            });
        } catch (e) {}
    }

    async function checkWhatsApp() {
        const inst = document.getElementById('msgInstanceId').value;
        const phone = document.getElementById('msgPhone').value;
        const resDiv = document.getElementById('checkResult');
        if(!inst || !phone) return;
        resDiv.innerHTML = '<span class="spinner-border spinner-border-sm text-info"></span>';
        try {
            const res = await fetch('ajax.php?action=check_numbers', {
                method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN},
                body: JSON.stringify({ instance: inst, numbers: [phone] })
            });
            const d = await res.json();
            let ex = (d.body && d.body[0] && d.body[0].exists);
            resDiv.innerHTML = ex ? '<span class="text-accent">✓ WhatsApp Ativo</span>' : '<span class="text-danger">✗ Sem WhatsApp</span>';
        } catch(e) { resDiv.innerHTML = 'Erro check.'; }
    }

    async function logoutInstance(id) { if(confirm('Sair do WhatsApp?')) { await fetch(`ajax.php?action=logout_instance&id=${id}`); loadInstances(); } }
    async function deleteInstance(id) { if(confirm('Apagar instância?')) { await fetch(`ajax.php?action=delete_instance&id=${id}`); loadInstances(); } }
    async function saveWebhook(id) { 
        const url = document.getElementById(`webhook-${id}`).value;
        await fetch('ajax.php?action=save_webhook', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ id, webhook_url: url }) });
        alert('Salvo!');
    }
    async function setPresence(p) { 
        const inst = document.getElementById('msgInstanceId').value;
        const phone = document.getElementById('msgPhone').value;
        if(!inst || !phone) return;
        fetch('ajax.php?action=set_presence', { method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN}, body: JSON.stringify({ instance: inst, recipient: phone, presence: p }) });
    }
    
// Função para mostrar/esconder o Token
function toggleToken() {
    const input = document.getElementById('apiTokenInput');
    const icon = document.getElementById('toggleIcon');
    
    if (input.type === "password") {
        input.type = "text";
        input.style.letterSpacing = "normal"; // Remove o espaçamento de "bolinhas"
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    } else {
        input.type = "password";
        input.style.letterSpacing = "2px";
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    }
}

// Função para copiar o Token
function copyToken() {
    const input = document.getElementById('apiTokenInput');
    
    // Seleciona o texto mesmo que esteja mascarado
    input.select();
    input.setSelectionRange(0, 99999); // Para dispositivos móveis
    
    navigator.clipboard.writeText(input.value).then(() => {
        // Feedback visual simples no botão
        const btn = event.currentTarget;
        const originalContent = btn.innerHTML;
        
        btn.innerHTML = '<i class="bi bi-check-lg text-accent"></i> Copiado!';
        btn.classList.add('border-accent');
        
        setTimeout(() => {
            btn.innerHTML = originalContent;
            btn.classList.remove('border-accent');
        }, 2000);
    }).catch(err => {
        console.error('Erro ao copiar: ', err);
    });
}
</script>
</body>
</html>
