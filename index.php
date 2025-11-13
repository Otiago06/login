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
    <title>Login - Sistema</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h2>Login</h2>
            <form id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="input-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="input-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required>
                </div>
                
                <button type="submit" class="btn">Entrar</button>
            </form>
            
            <div class="links">
                <a href="register.php">Criar nova conta</a>
                <a href="forgot_password.php">Esqueci minha senha</a>
            </div>
            
            <div id="message" class="message"></div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>