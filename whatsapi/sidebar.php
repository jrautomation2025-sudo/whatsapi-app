<?php
// sidebar.php - Versão Consolidada (JR TECH AUTO)
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    :root {
        --sidebar-w: 260px;
        --sidebar-bg: #0f1114;
        --accent: #00ff88; 
        --text-muted: #9ba3af;
        --border: #23272e;
        --nav-hover: #1f242c;
    }

    .jr-sidebar {
        width: var(--sidebar-w);
        background: var(--sidebar-bg);
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        border-right: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        z-index: 1000;
    }

    .jr-logo-area {
        padding: 25px;
        border-bottom: 1px solid var(--border);
    }

    .jr-logo-text {
        font-weight: 800;
        font-size: 1.1rem;
        color: #ffffff;
    }

    .text-accent { color: var(--accent) !important; }

    .jr-nav {
        padding: 15px 0;
        flex-grow: 1;
        overflow-y: auto;
    }

    .jr-nav-label {
        padding: 15px 25px 5px 25px;
        font-size: 0.7rem;
        text-transform: uppercase;
        font-weight: 700;
        color: #636e7b;
        letter-spacing: 0.5px;
    }

    .jr-nav-link {
        display: flex;
        align-items: center;
        padding: 10px 25px;
        color: var(--text-muted);
        text-decoration: none;
        font-size: 0.9rem;
        border-left: 3px solid transparent;
        transition: 0.2s;
    }

    .jr-nav-link i {
        width: 20px;
        margin-right: 12px;
        font-size: 1rem;
        text-align: center;
    }

    .jr-nav-link:hover {
        background: var(--nav-hover);
        color: #ffffff;
    }

    .jr-nav-link.active {
        background: rgba(0, 255, 136, 0.08);
        color: var(--accent);
        border-left-color: var(--accent);
        font-weight: 600;
    }

    .jr-user-footer {
        padding: 20px;
        border-top: 1px solid var(--border);
        background: #090a0c;
        margin-top: auto;
    }

    .user-badge {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
    }

    .user-avatar {
        width: 38px;
        height: 38px;
        min-width: 38px;
        border-radius: 10px;
        background: var(--accent);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #000000;
        font-weight: 800;
    }

    .user-info {
        overflow: hidden;
        line-height: 1.2;
    }

    .user-name {
        color: #ffffff;
        font-size: 0.85rem;
        font-weight: 600;
        white-space: nowrap;
        text-overflow: ellipsis;
        display: block;
    }

    .logout-link {
        color: #ff4d4d;
        font-size: 0.75rem;
        text-decoration: none;
        font-weight: 500;
    }

    .logout-link:hover { text-decoration: underline; }
</style>

<div class="jr-sidebar">
    <div class="jr-logo-area">
        <div class="jr-logo-text">
            <i class="fas fa-bolt text-accent me-1"></i>JR TECH <span class="text-accent">AUTO</span>
        </div>
        <div style="font-size: 0.65rem; color: #57606a;">SaaS Control Panel</div>
    </div>

    <div class="jr-nav">
        <div class="jr-nav-label">Gerenciamento</div>
        <a href="/whatsapi/painel" class="jr-nav-link <?= ($current_page == '/whatsapi/painel' || $current_page == '/whatsapi/dashboard') ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-pie"></i> Dashboard
        </a>
        <a href="/whatsapi/instancias" class="jr-nav-link <?= ($current_page == '/whatsapi/instancias') ? 'active' : '' ?>">
            <i class="fas fa-link"></i> Instâncias
        </a>

        <div class="jr-nav-label">Desenvolvedor</div>
        <a href="/whatsapi/docs" class="jr-nav-link <?= ($current_page == '/whatsapi/docs') ? 'active' : '' ?>">
            <i class="fas fa-code"></i> Documentação
        </a>
        <a href="/whatsapi/webhooks" class="jr-nav-link <?= ($current_page == '/whatsapi/webhooks') ? 'active' : '' ?>">
            <i class="fas fa-plug"></i> Webhooks
        </a>
        <a href="/whatsapi/logs" class="jr-nav-link <?= ($current_page == '/whatsapi/logs') ? 'active' : '' ?>">
            <i class="fas fa-terminal"></i> Logs de Mensagens
        </a>

        <div class="jr-nav-label">Financeiro</div>
        <a href="/whatsapi/financeiro" class="jr-nav-link <?= ($current_page == '/whatsapi/financeiro') ? 'active' : '' ?>">
            <i class="fas fa-wallet"></i> Planos
        </a>
    </div>

    <div class="jr-user-footer">
        <div class="user-badge">
            <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Everaldo') ?></span>
                <a href="/whatsapi/logout" class="logout-link">Encerrar Sessão</a>
            </div>
        </div>
    </div>
</div>
