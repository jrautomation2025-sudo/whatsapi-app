<?php
// register.php
require_once 'database.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if (!empty($nome) && !empty($email) && !empty($senha)) {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $api_token = bin2hex(random_bytes(32));

        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, api_token) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $senha_hash, $api_token]);
            
            $mensagem = "<div class='alert alert-success text-center'>Conta criada com sucesso! Você já pode fazer login.</div>";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $mensagem = "<div class='alert alert-danger text-center'>Este e-mail já está cadastrado no sistema.</div>";
            } else {
                $mensagem = "<div class='alert alert-danger text-center'>Erro ao cadastrar: " . $e->getMessage() . "</div>";
            }
        }
    } else {
        $mensagem = "<div class='alert alert-warning text-center'>Por favor, preencha todos os campos.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - JR Tech Automation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="jrtec.svg">
    <style>
        body { background-color: #121212; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .card { box-shadow: 0 4px 6px rgba(0,0,0,0.3); border: 1px solid #333; width: 100%; max-width: 400px; }
    </style>
</head>
<body>

<div class="card bg-dark text-light">
    <div class="card-header bg-primary text-white text-center border-bottom-0 py-3">
        <h5 class="mb-0 fw-bold">Criar Conta</h5>
    </div>
    <div class="card-body p-4">
        <?= $mensagem ?>
        <form method="POST" action="register.php">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome Completo ou Empresa</label>
                <input type="text" class="form-control bg-dark text-light border-secondary" id="nome" name="nome" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control bg-dark text-light border-secondary" id="email" name="email" required>
            </div>
            <div class="mb-4">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" class="form-control bg-dark text-light border-secondary" id="senha" name="senha" required>
            </div>
            <button type="submit" class="btn btn-success w-100 mb-3 fw-bold">Cadastrar</button>
            
            <div class="text-center mt-3">
                <a href="/whatsapi/login" class="text-decoration-none text-info">Já tem uma conta? Faça Login</a>
            </div>
            
            <hr class="border-secondary my-3">
            
            <div class="text-center">
                <a href="/" class="text-decoration-none text-secondary">&larr; Voltar para a página inicial</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
