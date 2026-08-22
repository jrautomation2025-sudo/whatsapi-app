<?php
// webhooks.php - Sincronizado e com Debug
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include 'sidebar.php'; 
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Webhooks - JR Tech Automation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/svg+xml" href="jrtec.svg">
    <style>
        :root { --bg-main: #0a0b0d; --card-bg: #14171c; --accent: #00ff88; --border: #30363d; }
        body { background: var(--bg-main); color: #ffffff; font-family: 'Inter', sans-serif; }
        .main-wrapper { margin-left: 260px; padding: 40px; min-height: 100vh; }
        .doc-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; padding: 30px; margin-bottom: 25px; }
        h2, h4 { color: #ffffff !important; font-weight: 800; }
        .label-white { color: #ffffff !important; font-size: 0.85rem; font-weight: 700; margin-bottom: 8px; display: block; text-transform: uppercase; }
        input.form-control { background: #0d1117 !important; border: 1px solid var(--border) !important; color: #ffffff !important; }
        .btn-save { background: var(--accent); color: #000; font-weight: 800; border: none; padding: 10px 25px; }
    </style>
</head>
<body>

<div class="main-wrapper">
    <div class="mb-5">
        <h2 class="text-white">Configurações de Webhooks</h2>
        <p class="text-white">Gerencie o destino das notificações na plataforma.</p>
    </div>

    <div class="row" id="webhookGrid">
        <div class="col-12 text-center py-5"><div class="spinner-border text-accent"></div></div>
    </div>
</div>

<script>
    // Forçamos a leitura do token da sessão PHP para o JS
    const CSRF_TOKEN = "<?php echo $_SESSION['csrf_token'] ?? ''; ?>";
    console.log("Token carregado no JS:", CSRF_TOKEN); // Para você ver se não está vazio

    document.addEventListener("DOMContentLoaded", loadWebhooks);

    async function loadWebhooks() {
        const container = document.getElementById('webhookGrid');
        try {
            const res = await fetch('ajax.php?action=list_instances');
            const data = await res.json();
            container.innerHTML = '';
            
            data.forEach(inst => {
                // Verificamos se já existe configuração salva (ou usamos padrão)
                const isBase64 = inst.webhook_base64 == 1 ? 'checked' : '';
                
                container.innerHTML += `
                    <div class="col-lg-6 mb-4">
                        <div class="doc-card">
                            <h4 class="text-white mb-4">${inst.display_name}</h4>
                            
                            <div class="mb-4">
                                <span class="label-white">URL do Webhook (n8n)</span>
                                <input type="url" id="webhook-${inst.instance_name}" class="form-control mb-3" 
                                       placeholder="https://sua-url-n8n.com" value="${inst.webhook_url || ''}">
                            </div>

                            <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-3 mb-3">
                                <div>
                                    <span class="text-white fw-bold d-block">Webhook Base64</span>
                                    <small class="text-white opacity-50">Enviar mídia codificada no JSON</small>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="base64-${inst.instance_name}" ${isBase64} style="transform: scale(1.5); cursor: pointer;">
                                </div>
                            </div>

                            <div class="mb-4">
                                <span class="label-white mb-2">Eventos Disponíveis</span>
                                <div class="p-3 bg-dark rounded border border-secondary">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" checked disabled id="event-upsert-${inst.instance_name}">
                                        <label class="form-check-label text-white small">MESSAGES_UPSERT (Obrigatório)</label>
                                    </div>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="event-status-${inst.instance_name}">
                                        <label class="form-check-label text-white small">PRESENCE_UPDATE (Status Online)</label>
                                    </div>
                                </div>
                            </div>

                            <button class="btn btn-save w-100" onclick="updateWebhook('${inst.instance_name}')">
                                <i class="bi bi-cloud-arrow-up-fill me-2"></i>SALVAR CONFIGURAÇÕES
                            </button>
                        </div>
                    </div>`;
            });
        } catch (e) { container.innerHTML = '<p class="text-white">Erro ao listar instâncias.</p>'; }
    }

    async function updateWebhook(instanceName) {
        const urlValue = document.getElementById(`webhook-${instanceName}`).value;
        const base64Value = document.getElementById(`base64-${instanceName}`).checked;
        const btn = event.currentTarget;
        
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> SALVANDO...';

        try {
            const res = await fetch('ajax.php?action=save_webhook', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                body: JSON.stringify({ 
                    id: instanceName, 
                    webhook_url: urlValue,
                    base64: base64Value 
                })
            });

            const result = await res.json();
            if(result.success) {
                alert('Configurações aplicadas com sucesso!');
            } else {
                alert('Erro ao salvar: ' + (result.error || 'Falha na API'));
            }
        } catch (e) {
            alert('Erro de rede ou servidor.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cloud-arrow-up-fill me-2"></i>SALVAR CONFIGURAÇÕES';
        }
    }
</script>
</body>
</html>
