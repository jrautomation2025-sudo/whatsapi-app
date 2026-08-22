<?php
// instancias.php - Gerenciamento de Conexões
session_start();
require_once 'database.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

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
    <title>Instâncias - JR Tech Automation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/svg+xml" href="jrtec.svg">
    <style>
        :root { --bg-main: #0a0b0d; --card-bg: #14171c; --accent: #00ff88; --border: #23272e; --text-pure: #ffffff; }
        body { background: var(--bg-main); color: var(--text-pure); font-family: 'Inter', sans-serif; }
        .main-wrapper { margin-left: 260px; padding: 40px; min-height: 100vh; }
        .doc-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; padding: 25px; margin-bottom: 25px; }
        .status-pill { font-size: 0.7rem; padding: 3px 12px; border-radius: 20px; border: 1px solid; font-weight: 800; text-transform: uppercase; }
        .status-online { background: rgba(0, 255, 136, 0.1); color: var(--accent); border-color: var(--accent); }
        .status-offline { background: rgba(255, 77, 77, 0.1); color: #ff4d4d; border-color: #ff4d4d; }
        .btn-accent { background: var(--accent); color: #000; font-weight: 800; border: none; }
        .table-dark { --bs-table-bg: transparent; color: #ced4da; }
        .qr-container { background: #fff; padding: 15px; border-radius: 8px; display: inline-block; }
    </style>
</head>
<body>

<div class="main-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-0">Gerenciar Instâncias</h2>
            <p class="text-white small">Visualize e conecte seus números de WhatsApp.</p>
        </div>
        <?php if ($instancias >= 3): ?>
            <button class="btn btn-secondary px-4" disabled title="Limite atingido">
                <i class="bi bi-plus-lg me-2"></i>Nova Instância
            </button>
        <?php else: ?>
            <button class="btn btn-accent px-4" data-bs-toggle="modal" data-bs-target="#modalCreate">
                <i class="bi bi-plus-lg me-2"></i>Nova Instância
            </button>
        <?php endif; ?>
    </div>

    <div class="doc-card">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th class="text-white small uppercase">NOME AMIGÁVEL</th>
                        <th class="text-white small uppercase">ID TÉCNICO</th>
                        <th class="text-white small uppercase">STATUS</th>
                        <th class="text-white small uppercase text-end">AÇÕES</th>
                    </tr>
                </thead>
                <tbody id="fullInstanceList">
                    </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCreate" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-secondary">
            <form id="formNewInstance">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold">Nova Instância</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="small-label mb-2 d-block text-muted">Nome de Identificação</label>
                    <input type="text" id="newName" class="form-control bg-dark text-white border-secondary" placeholder="Ex: Suporte Vendas" required>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="submit" class="btn btn-accent w-100">CRIAR AGORA</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="qrModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-secondary">
            <div class="modal-header border-secondary">
                <h6 class="modal-title fw-bold">Conectar WhatsApp</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4" id="qrModalBody"></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const CSRF_TOKEN = "<?= $_SESSION['csrf_token'] ?? '' ?>";
    let qrModal = null;
    let pollInterval = null;

    document.addEventListener("DOMContentLoaded", () => {
        qrModal = new bootstrap.Modal(document.getElementById('qrModal'));
        loadFullInstances();
    });

    async function loadFullInstances() {
        const tbody = document.getElementById('fullInstanceList');
        try {
            const res = await fetch('ajax.php?action=list_instances');
            const data = await res.json();
            tbody.innerHTML = '';
            data.forEach(inst => {
                const isOpen = (inst.api_status === 'open' || inst.api_status === 'connected');
                tbody.innerHTML += `
                    <tr>
                        <td class="fw-bold text-white">${inst.display_name}</td>
                        <td class="text-white small">${inst.instance_name}</td>
                        <td><span class="status-pill ${isOpen ? 'status-online' : 'status-offline'}">${isOpen ? 'Online' : 'Offline'}</span></td>
                        <td class="text-end">
                            ${isOpen ? 
                                `<button class="btn btn-sm btn-outline-warning me-2" onclick="logoutInst('${inst.instance_name}')">Logout</button>` : 
                                `<button class="btn btn-sm btn-success me-2 text-dark fw-bold" onclick="connectInst('${inst.instance_name}')">Conectar</button>`
                            }
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteInst('${inst.instance_name}')"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>`;
            });
        } catch (e) { tbody.innerHTML = 'Erro ao carregar.'; }
    }

    async function connectInst(id) {
        const body = document.getElementById('qrModalBody');
        body.innerHTML = '<div class="spinner-border text-accent mb-3"></div><p>Gerando QR Code...</p>';
        qrModal.show();
        try {
            const res = await fetch(`ajax.php?action=connect_instance&id=${id}`);
            const data = await res.json();
            console.log('connect_instance response', data);

            if (data.connected === true || data.status === 'connected' || data.status === 'open') {
                body.innerHTML = '<div class="alert alert-success">Já conectado!</div>';
                setTimeout(() => { qrModal.hide(); loadFullInstances(); }, 1200);
                return;
            }

            const qr = data.base64 || data.qrcode || data.qr || data.code || (data.data && (data.data.base64 || data.data.qrcode || data.data.qr));
            if (qr) {
                let qrSrc = qr;
                if (!qrSrc.startsWith('data:')) {
                    qrSrc = 'data:image/png;base64,' + qrSrc;
                }
                body.innerHTML = `<div class="qr-container"><img src="${qrSrc}" class="img-fluid"></div><p class="mt-3 text-accent small fw-bold">Escaneie para conectar</p>`;

                if (pollInterval) clearInterval(pollInterval);
                pollInterval = setInterval(async () => {
                    const r = await fetch(`ajax.php?action=check_status&id=${id}`);
                    const d = await r.json();
                    if (d.status === 'open' || d.status === 'connected') {
                        clearInterval(pollInterval);
                        body.innerHTML = '<div class="alert alert-success">Conectado!</div>';
                        setTimeout(() => { qrModal.hide(); loadFullInstances(); }, 1500);
                    }
                }, 3000);

                return;
            }

            // não veio QR, exibe texto do backend
            const message = data.message || data.error || JSON.stringify(data);
            body.innerHTML = `<div class="alert alert-warning">${message}</div>`;
        } catch (e) {
            body.innerHTML = '<div class="alert alert-danger">Erro de conexão: ' + e.message + '</div>';
        }
    }

    document.getElementById('formNewInstance').addEventListener('submit', async (e) => {
        e.preventDefault();
        const name = document.getElementById('newName').value;
        try {
            const res = await fetch('ajax.php?action=create_instance', {
                method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN},
                body: JSON.stringify({ name })
            });

            const text = await res.text();
            let data = null;
            try {
                data = JSON.parse(text);
            } catch (_) {
                throw new Error('Resposta inválida do servidor: ' + text);
            }

            if (!res.ok) {
                alert('Erro ao criar instância: ' + (data.error || JSON.stringify(data)));
                return;
            }
        } catch (err) {
            alert('Erro de rede ao criar instância: ' + err.message);
            return;
        }

        bootstrap.Modal.getInstance(document.getElementById('modalCreate')).hide();
        loadFullInstances();
    });

    async function logoutInst(id) { 
        if (!confirm('Sair?')) return;
        await fetch(`ajax.php?action=logout_instance&id=${id}`, {
            method: 'POST', headers: {'X-CSRF-Token': CSRF_TOKEN}
        });
        loadFullInstances();
    }

    async function deleteInst(id) { 
        if (!confirm('Apagar?')) return;
        await fetch(`ajax.php?action=delete_instance&id=${id}`, {
            headers: {'X-CSRF-Token': CSRF_TOKEN}
        });
        loadFullInstances();
    }
</script>
</body>
</html>
