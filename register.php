<?php
include 'config.php';
include 'security.php';

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta - Sistema</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="register-box">
            <h2>Criar Nova Conta</h2>
            <form id="registerForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="input-group">
                    <label for="nome">Nome Completo</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                
                <div class="input-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="input-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required minlength="6">
                    <small style="color: #666; font-size: 12px;">A senha deve ter pelo menos 6 caracteres</small>
                </div>
                
                <div class="input-group">
                    <label for="confirmar_senha">Confirmar Senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required>
                </div>
                
                <button type="submit" class="btn">Criar Conta</button>
            </form>
            
            <div class="links">
                <a href="index.php">Já tem uma conta? Faça login</a>
            </div>
            
            <div id="message" class="message"></div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>