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
    <title>Recuperar Senha - Sistema</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h2>Recuperar Senha</h2>
            <form id="recoveryForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="input-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <button type="submit" class="btn">Enviar Link de Recuperação</button>
            </form>
            
            <div class="links">
                <a href="index.php">Voltar para o login</a>
            </div>
            
            <div id="message" class="message"></div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const recoveryForm = document.getElementById('recoveryForm');
        if (recoveryForm) {
            recoveryForm.addEventListener('submit', function(e) {
                e.preventDefault();
                handleRecovery();
            });
        }
    });

    function handleRecovery() {
        const form = document.getElementById('recoveryForm');
        const formData = new FormData(form);
        const messageDiv = document.getElementById('message');
        
        fetch('recovery_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data.startsWith('success')) {
                messageDiv.className = 'message success';
                messageDiv.textContent = 'Link de recuperação enviado para seu e-mail!';
            } else {
                messageDiv.className = 'message error';
                messageDiv.textContent = data.replace('error: ', '');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messageDiv.className = 'message error';
            messageDiv.textContent = 'Erro ao processar solicitação. Tente novamente.';
        });
    }
    </script>
</body>
</html>