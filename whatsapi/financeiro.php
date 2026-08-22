<?php
// financeiro.php - Gestão de Assinaturas e Faturas (Alto Contraste)
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 1. Busca dados do plano do usuário
$stmt = $pdo->prepare("SELECT plano, expira_em FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_data = $stmt->fetch();

$hoje = new DateTime();
$expiracao = new DateTime($user_data['expira_em']);
$dias_restantes = ($hoje > $expiracao) ? 0 : (int)$hoje->diff($expiracao)->format("%a");

include 'sidebar.php'; 
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Financeiro - JR Tech Automation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/svg+xml" href="jrtec.svg">
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
            padding: 30px; 
            margin-bottom: 25px; 
        }

        h2, h4, h5, .text-white { color: #ffffff !important; font-weight: 800; }
        
        .plan-price { font-size: 2rem; font-weight: 900; color: var(--accent); }
        .plan-feature { color: #ffffff; font-size: 0.9rem; margin-bottom: 10px; list-style: none; padding: 0; }
        .plan-feature li::before { content: "✓"; color: var(--accent); margin-right: 10px; font-weight: bold; }

        .status-badge {
            font-size: 0.75rem;
            font-weight: 800;
            padding: 5px 15px;
            border-radius: 50px;
            text-transform: uppercase;
        }

        .btn-pay { 
            background: var(--accent); 
            color: #000; 
            font-weight: 900; 
            border: none; 
            width: 100%;
            padding: 12px;
            transition: 0.3s;
        }
        .btn-pay:hover { background: #00e67a; transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0, 255, 136, 0.3); }

        .table-dark { --bs-table-bg: transparent; color: #ffffff; }
        .table-dark thead th { color: #ffffff; border-bottom: 2px solid var(--border); font-size: 0.75rem; text-transform: uppercase; }
    </style>
</head>
<body>

<div class="main-wrapper">
    <div class="mb-5">
        <h2 class="text-white">Meu Plano e Financeiro</h2>
        <p class="text-white">Gerencie sua assinatura e visualize seu histórico de pagamentos.</p>
    </div>

    <div class="doc-card border-accent" style="border-width: 2px; background: rgba(0, 255, 136, 0.02);">
        <div class="row align-items-center text-center text-md-start">
            <div class="col-md-8">
                <h5 class="text-white mb-1">Seu Plano Atual: <span class="text-accent"><?= strtoupper($user_data['plano']) ?></span></h5>
                <p class="text-white mb-0">Sua assinatura expira em: <strong><?= date('d/m/Y', strtotime($user_data['expira_em'])) ?></strong></p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <span class="status-badge <?= ($dias_restantes > 0) ? 'bg-success text-dark' : 'bg-danger text-white' ?>">
                    <?= ($dias_restantes > 0) ? "$dias_restantes DIAS RESTANTES" : "ASSINATURA EXPIRADA" ?>
                </span>
            </div>
        </div>
    </div>

    <h4 class="text-white mb-4 mt-5">Planos Disponíveis</h4>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="doc-card h-100 text-center">
                <h5 class="text-white">Plano Mensal</h5>
                <div class="plan-price my-3">R$ 49,90</div>
                <ul class="plan-feature text-start mx-auto d-inline-block">
                    <li>3 Instâncias</li>
                    <li>API de Texto e Mídia</li>
                    <li>Atendimento Por Chat</li>
                </ul>
                <button class="btn btn-pay mt-4" onclick="generatePix(19.90, 'Mensal')">ASSINAR MENSAL</button>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="doc-card h-100 text-center border-accent" style="transform: scale(1.05);">
                <span class="badge bg-accent text-start mb-2">MAIS POPULAR</span>
                <h5 class="text-white">Plano Trimestral</h5>
                <div class="plan-price my-3">R$ 134,70</div>
                <ul class="plan-feature text-start mx-auto d-inline-block">
                    <li>Economia de 10%</li>
                    <li>Acesso Total à API</li>
                    <li>Consultoria de Automação</li>
                </ul>
                <button class="btn btn-pay mt-4" onclick="generatePix(53.70, 'Trimestral')">ASSINAR TRIMESTRAL</button>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="doc-card h-100 text-center">
                <h5 class="text-white">Plano Anual</h5>
                <div class="plan-price my-3">R$ 499,90</div>
                <ul class="plan-feature text-start mx-auto d-inline-block">
                    <li>2 Meses Grátis</li>
                    <li>Suporte Prioritário</li>
                    <li>Atendimento 24x7</li>
                </ul>
                <button class="btn btn-pay mt-4" onclick="generatePix(199.90, 'Anual')">ASSINAR ANUAL</button>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", loadInvoices);

    async function loadInvoices() {
        const tbody = document.getElementById('invoiceTable');
        try {
            const res = await fetch('ajax.php?action=get_invoices');
            const data = await res.json();
            tbody.innerHTML = '';

            if(data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-white py-4">Nenhuma fatura encontrada.</td></tr>';
                return;
            }

            data.forEach(inv => {
                const statusColor = inv.status === 'paid' ? 'text-accent' : 'text-warning';
                tbody.innerHTML += `
                    <tr>
                        <td class="text-white">#${inv.id}</td>
                        <td class="text-white fw-bold">${inv.plan}</td>
                        <td class="text-white">R$ ${inv.amount}</td>
                        <td class="text-white">${new Date(inv.created_at).toLocaleDateString('pt-BR')}</td>
                        <td class="${statusColor} fw-bold">${inv.status.toUpperCase()}</td>
                        <td class="text-end">
                            ${inv.status === 'pending' ? '<button class="btn btn-sm btn-outline-info" onclick="payAgain('+inv.id+')">Pagar</button>' : '-'}
                        </td>
                    </tr>`;
            });
        } catch (e) { tbody.innerHTML = '<tr><td colspan="6">Erro ao carregar faturas.</td></tr>'; }
    }

    async function generatePix(valor, plano) {
        if(!confirm('Deseja gerar o pagamento PIX para o plano ' + plano + '?')) return;
        
        // Aqui chamaremos o seu futuro checkout.php ou rota ajax para criar a fatura
        alert('Integrando com API de Pagamento...\nPlano: ' + plano + '\nValor: R$ ' + valor);
    }
</script>

</body>
</html>
