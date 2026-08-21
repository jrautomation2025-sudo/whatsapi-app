<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp API Manager - JR Tech Automation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { 
            background-color: #0f1015; 
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; 
        }
        .hero-section { 
            padding: 120px 0 80px; 
            background: radial-gradient(circle at top right, #1a2035 0%, #0f1015 60%); 
        }
        .highlight-text { color: #0d6efd; }
        
        /* Estilos dos Cards de Preço */
        .pricing-card { 
            transition: transform 0.3s ease, box-shadow 0.3s ease; 
            border: 1px solid #2d323f; 
            background-color: #161921; 
            height: 100%;
            position: relative;
        }
        .pricing-card:hover { 
            transform: translateY(-10px); 
            border-color: #0d6efd; 
            box-shadow: 0 10px 30px rgba(13, 110, 253, 0.15);
        }
        .pricing-card.popular {
            border-color: #198754;
        }
        .badge-discount { 
            position: absolute; 
            top: -15px; 
            right: 20px; 
            background-color: #198754; 
            color: white; 
            padding: 5px 15px; 
            border-radius: 20px; 
            font-weight: bold; 
            font-size: 0.9rem; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.2); 
        }
        .feature-icon { font-size: 2.5rem; color: #0d6efd; margin-bottom: 1rem; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom border-secondary sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="#">JR Tech Automation</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#recursos">Recursos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#planos">Planos</a></li>
                    <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                        <a class="btn btn-outline-light w-100" href="/whatsapi/login">Entrar</a>
                    </li>
                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <a class="btn btn-primary w-100" href="/whatsapi/register">Teste Grátis</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Escale seu negócio com a nossa <br><span class="highlight-text">API de WhatsApp</span></h1>
            <p class="lead text-secondary mb-5 mx-auto" style="max-width: 700px;">
                Crie instâncias ilimitadas, conecte seus números via QR Code e automatize seus atendimentos, disparos e webhooks de forma simples e estável.
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="/whatsapi/register" class="btn btn-primary btn-lg px-4 rounded-pill">Começar Teste Grátis de 7 Dias</a>
                <a href="#planos" class="btn btn-outline-light btn-lg px-4 rounded-pill">Ver Planos</a>
            </div>
            <p class="mt-3 text-muted small"><i class="bi bi-credit-card-2-front"></i> Não exigimos cartão de crédito para testar.</p>
        </div>
    </section>

    <section id="recursos" class="py-5 bg-dark">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Tudo que você precisa em uma única plataforma</h2>
                <p class="text-secondary">Desenvolvido para conectar seu negócio ao aplicativo de mensagens mais usado do mundo.</p>
            </div>
            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="p-4 border border-secondary rounded h-100 bg-dark">
                        <i class="bi bi-send feature-icon"></i>
                        <h4 class="fw-bold">Envio Multimídia</h4>
                        <p class="text-secondary">Envie textos, imagens, áudios e documentos via API com latência quase zero para seus clientes.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4 border border-secondary rounded h-100 bg-dark">
                        <i class="bi bi-diagram-3 feature-icon"></i>
                        <h4 class="fw-bold">Integração Total</h4>
                        <p class="text-secondary">Configure Webhooks facilmente para receber mensagens e conecte com plataformas como n8n, Typebot e CRM.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4 border border-secondary rounded h-100 bg-dark">
                        <i class="bi bi-shield-check feature-icon"></i>
                        <h4 class="fw-bold">Painel Independente</h4>
                        <p class="text-secondary">Gere seus próprios tokens de API, conecte instâncias isoladas e gerencie sua conexão com total segurança.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="planos" class="py-5" style="background-color: #0f1015;">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Escolha o plano ideal para você</h2>
                <p class="text-secondary">Transparência total. Cancele quando quiser.</p>
            </div>
            
            <div class="row justify-content-center g-4">
                
                <div class="col-lg-4 col-md-6">
                    <div class="card pricing-card rounded-4 p-4">
                        <div class="card-body">
                            <h4 class="card-title fw-bold text-light mb-3">Mensal</h4>
                            <h2 class="display-5 fw-bold text-white mb-0">R$ 49,90<span class="fs-5 text-secondary fw-normal">/mês</span></h2>
                            <p class="text-success mt-2 mb-4 fw-bold"><i class="bi bi-gift"></i> 7 dias de teste grátis</p>
                            
                            <ul class="list-unstyled text-secondary mb-4">
                                <li class="mb-2"><i class="bi bi-check2 text-primary me-2"></i> 3 Instância</li>
                                <li class="mb-2"><i class="bi bi-check2 text-primary me-2"></i> Envio de Texto e Mídia</li>
                                <li class="mb-2"><i class="bi bi-check2 text-primary me-2"></i> Webhooks em tempo real</li>
                                <li class="mb-2"><i class="bi bi-check2 text-primary me-2"></i> Token de API exclusivo</li>
                                <li class="mb-2"><i class="bi bi-check2 text-primary me-2"></i> Suporte via ticket</li>
                            </ul>
                            
                            <a href="/whatsapi/register?plano=mensal" class="btn btn-outline-primary w-100 py-2 rounded-3 fw-bold">Assinar Mensal</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="card pricing-card popular rounded-4 p-4">
                        <div class="badge-discount">Pague 10, Use 12 meses!</div>
                        <div class="card-body">
                            <h4 class="card-title fw-bold text-light mb-3">Anual <span class="badge bg-success ms-2 fs-6">Recomendado</span></h4>
                            <h2 class="display-5 fw-bold text-white mb-0">R$ 499,90<span class="fs-5 text-secondary fw-normal">/ano</span></h2>
                            <p class="text-success mt-2 mb-4 fw-bold"><i class="bi bi-gift"></i> 7 dias de teste grátis</p>
                            
                            <ul class="list-unstyled text-secondary mb-4">
                                <li class="mb-2"><i class="bi bi-check2-all text-success me-2"></i> <strong>2 meses de desconto</strong></li>
                                <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i> Suporte Prioritário</li>
                                <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i> Envio de Texto e Mídia</li>
                                <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i> Webhooks em tempo real</li>
                                <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i> Token de API exclusivo</li>
                            </ul>
                            
                            <a href="/whatsapi/register?plano=anual" class="btn btn-success w-100 py-2 rounded-3 fw-bold">Assinar Anual</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <footer class="bg-dark border-top border-secondary py-4 mt-auto">
        <div class="container text-center text-secondary">
            <p class="mb-1">&copy; 2026 JR Tech Automation. Todos os direitos reservados.</p>
            <p class="mb-0 small"><i class="bi bi-whatsapp"></i> Suporte: (81) 98420-1425</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>