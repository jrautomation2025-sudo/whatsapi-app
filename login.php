<?php
// login.php
session_start();
require_once 'database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: /whatsapi/painel");
    exit;
}

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if (!empty($email) && !empty($senha)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($senha, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['api_token'] = $user['api_token']; 
                
                header("Location: /whatsapi/painel");
                exit;
            } else {
                $mensagem = "<div class='alert alert-danger text-center'>E-mail ou senha incorretos.</div>";
            }
        } catch (PDOException $e) {
            $mensagem = "<div class='alert alert-danger text-center'>Erro no sistema: " . $e->getMessage() . "</div>";
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
    <title>Login - JR Tech Automation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #121212; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .card { box-shadow: 0 4px 6px rgba(0,0,0,0.3); border: 1px solid #333; width: 100%; max-width: 400px; }
    </style>
</head>
<body>

<div class="card bg-dark text-light">
    <div class="card-header bg-success text-white text-center border-bottom-0 py-3">
        <h5 class="mb-0 fw-bold">Acessar Painel</h5>
    </div>
    <div class="card-body p-4">
        <?= $mensagem ?>
        
        <form method="POST" action="login.php">
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control bg-dark text-light border-secondary" id="email" name="email" required>
            </div>
            <div class="mb-4">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" class="form-control bg-dark text-light border-secondary" id="senha" name="senha" required>
            </div>
            <button type="submit" class="btn btn-success w-100 mb-3 fw-bold">Entrar</button>
            
            <div class="text-center mt-3">
                <a href="/whatsapi/register" class="text-decoration-none text-info">Ainda não tem conta? Inicie seu Teste Grátis</a>
            </div>

            <hr class="border-secondary my-3">
            
            <div class="text-center">
                <a href="/whatsapi/" class="text-decoration-none text-secondary">&larr; Voltar para a página inicial</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>