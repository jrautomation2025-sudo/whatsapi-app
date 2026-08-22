<?php
session_start();
require_once 'database.php';
if (!isset($_SESSION['user_id'])) { header("Location: /whatsapi/login"); exit; }
include 'sidebar.php'; 
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - JR Tech Automation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="jrtec.svg">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --bg-main: #0a0b0d; --card-bg: #14171c; --accent: #00ff88; --border: #30363d; }
        body { background: var(--bg-main); color: #ffffff; font-family: 'Inter', sans-serif; }
        .main-wrapper { margin-left: 260px; padding: 40px; }
        .stat-card { 
            background: var(--card-bg); 
            border: 1px solid var(--border); 
            border-radius: 12px; 
            padding: 20px;
            transition: 0.3s;
        }
        .stat-card:hover { border-color: var(--accent); }
        .stat-val { font-size: 2rem; font-weight: 800; color: var(--accent); }
        .chart-container { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; padding: 25px; margin-top: 30px; }
    </style>
</head>
<body>

<div class="main-wrapper">
    <h2 class="text-white mb-4">Bem-vindo, <?= $_SESSION['user_name'] ?? 'Everaldo' ?></h2>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="stat-card">
                <span class="text-white opacity-50 small text-uppercase fw-bold">Total Enviado (7d)</span>
                <div class="stat-val" id="totalSent">0</div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="stat-card">
                <span class="text-white opacity-50 small text-uppercase fw-bold">Taxa de Sucesso</span>
                <div class="stat-val" id="successRate">0%</div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="stat-card">
                <span class="text-white opacity-50 small text-uppercase fw-bold">Instâncias Ativas</span>
                <div class="stat-val" id="activeInst">...</div>
            </div>
        </div>
    </div>

    <div class="chart-container">
        <h4 class="text-white mb-4">Volume de Mensagens</h4>
        <canvas id="msgChart" style="max-height: 350px;"></canvas>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", async () => {
    const res = await fetch('ajax.php?action=get_dashboard_stats');
    const data = await res.json();

    const labels = data.map(d => new Date(d.data).toLocaleDateString('pt-BR', {day:'2-digit', month:'2-digit'}));
    const totals = data.map(d => d.total);
    const sucessos = data.map(d => d.sucessos);

    // Atualiza Cards
    const totalGeral = totals.reduce((a, b) => a + parseInt(b), 0);
    const totalSucesso = sucessos.reduce((a, b) => a + parseInt(b), 0);
    document.getElementById('totalSent').innerText = totalGeral;
    document.getElementById('successRate').innerText = totalGeral > 0 ? Math.round((totalSucesso/totalGeral)*100) + '%' : '0%';

    // Renderiza Gráfico
    const ctx = document.getElementById('msgChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Mensagens processadas',
                data: totals,
                borderColor: '#00ff88',
                backgroundColor: 'rgba(0, 255, 136, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#00ff88'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { color: '#30363d' }, ticks: { color: '#888' } },
                x: { grid: { display: false }, ticks: { color: '#888' } }
            }
        }
    });
});
</script>
</body>
</html>
