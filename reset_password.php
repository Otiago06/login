<?php
include 'config.php';
include 'security.php';

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: dashboard.php");
    exit;
}

$token = $_GET['token'] ?? '';

if (empty($token)) {
    header("Location: forgot_password.php");
    exit;
}

// Verificar se o token é válido
$database = new Database();
$db = $database->getConnection();

$query = "SELECT id FROM usuarios WHERE token_recuperacao = :token AND token_expiracao > NOW()";
$stmt = $db->prepare($query);
$stmt->bindParam(":token", $token);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    header("Location: forgot_password.php?error=token_invalido");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - Sistema</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h2>Redefinir Senha</h2>
            <form id="resetPasswordForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="input-group">
                    <label for="nova_senha">Nova Senha</label>
                    <input type="password" id="nova_senha" name="nova_senha" required minlength="6">
                </div>
                
                <div class="input-group">
                    <label for="confirmar_senha">Confirmar Nova Senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required>
                </div>
                
                <button type="submit" class="btn">Redefinir Senha</button>
            </form>
            
            <div class="links">
                <a href="index.php">Voltar para o login</a>
            </div>
            
            <div id="message" class="message"></div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const resetForm = document.getElementById('resetPasswordForm');
        if (resetForm) {
            resetForm.addEventListener('submit', function(e) {
                e.preventDefault();
                handleResetPassword();
            });
        }
    });

    function handleResetPassword() {
        const form = document.getElementById('resetPasswordForm');
        const formData = new FormData(form);
        const messageDiv = document.getElementById('message');
        
        // Verificar se as senhas coincidem
        const novaSenha = form.querySelector('#nova_senha').value;
        const confirmarSenha = form.querySelector('#confirmar_senha').value;
        
        if (novaSenha !== confirmarSenha) {
            messageDiv.className = 'message error';
            messageDiv.textContent = 'As senhas não coincidem.';
            return;
        }
        
        fetch('reset_password_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data.startsWith('success')) {
                messageDiv.className = 'message success';
                messageDiv.textContent = 'Senha redefinida com sucesso! Redirecionando...';
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 2000);
            } else {
                messageDiv.className = 'message error';
                messageDiv.textContent = data.replace('error: ', '');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messageDiv.className = 'message error';
            messageDiv.textContent = 'Erro ao redefinir senha. Tente novamente.';
        });
    }
    </script>
</body>
</html>