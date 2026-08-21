<?php
// logs.php - Relatório de Mensagens Enviadas
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'sidebar.php'; 
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Logs de Mensagens - JR Tech Automation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { 
            --bg-main: #0a0b0d; 
            --card-bg: #14171c; 
            --accent: #00ff88; 
            --border: #30363d; 
        }
        body { background: var(--bg-main); color: #ffffff; font-family: 'Inter', sans-serif; }
        .main-wrapper { margin-left: 260px; padding: 40px; min-height: 100vh; }
        
        .doc-card { 
            background: var(--card-bg); 
            border: 1px solid var(--border); 
            border-radius: 12px; 
            padding: 25px; 
        }

        h2, .text-white { color: #ffffff !important; font-weight: 800; }
        
        .table-dark { --bs-table-bg: transparent; color: #ffffff; }
        .table-dark thead th { 
            color: #ffffff; 
            border-bottom: 2px solid var(--border); 
            font-size: 0.75rem; 
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-badge {
            font-size: 0.7rem;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .bg-success-custom { background: rgba(0, 255, 136, 0.15); color: #00ff88; border: 1px solid #00ff88; }
        .bg-danger-custom { background: rgba(255, 69, 58, 0.15); color: #ff453a; border: 1px solid #ff453a; }

        .refresh-btn {
            background: transparent;
            border: 1px solid var(--border);
            color: #ffffff;
            transition: 0.3s;
        }
        .refresh-btn:hover { border-color: var(--accent); color: var(--accent); }
    </style>
</head>
<body>

<div class="main-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="text-white">Relatórios de Envios</h2>
            <p class="text-white opacity-75">Histórico em tempo real das mensagens processadas via API e Automação.</p>
        </div>
        <button class="btn refresh-btn" onclick="loadLogs()">
            <i class="bi bi-arrow-clockwise me-2"></i>Atualizar
        </button>
    </div>

    <div class="doc-card">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Instância</th>
                        <th>Destinatário</th>
                        <th>Tipo</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="logsTableBody">
                    <tr><td colspan="5" class="text-center py-5">Carregando logs...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", loadLogs);

    async function loadLogs() {
        const tbody = document.getElementById('logsTableBody');
        try {
            const res = await fetch('ajax.php?action=get_logs');
            const data = await res.json();
            
            if(data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-white">Nenhum log encontrado ainda.</td></tr>';
                return;
            }

            tbody.innerHTML = '';
            data.forEach(log => {
                const statusClass = log.status === 'Enviado' ? 'bg-success-custom' : 'bg-danger-custom';
                const dateFormated = new Date(log.sent_at).toLocaleString('pt-BR');
                
                tbody.innerHTML += `
                    <tr>
                        <td class="text-white small">${dateFormated}</td>
                        <td class="text-white fw-bold">${log.display_name}</td>
                        <td class="text-white">${log.destination_phone}</td>
                        <td><span class="badge bg-dark border border-secondary">${log.message_type.toUpperCase()}</span></td>
                        <td><span class="status-badge ${statusClass}">${log.status}</span></td>
                    </tr>`;
            });
        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Erro ao carregar dados.</td></tr>';
        }
    }

    // Atualiza automaticamente a cada 30 segundos
    setInterval(loadLogs, 30000);
</script>

</body>
</html>