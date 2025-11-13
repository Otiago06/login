<?php
include 'config.php';
include 'security.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        die("Token CSRF inválido");
    }
    
    $token = sanitizeInput($_POST['token']);
    $nova_senha = $_POST['nova_senha'];
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Verificar se o token é válido
    $query = "SELECT id FROM usuarios WHERE token_recuperacao = :token AND token_expiracao > NOW()";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":token", $token);
    $stmt->execute();
    
    if ($stmt->rowCount() == 1) {
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Atualizar senha
        $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        
        $query = "UPDATE usuarios SET senha_hash = :senha_hash, token_recuperacao = NULL, token_expiracao = NULL, tentativas_login = 0 WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":senha_hash", $nova_senha_hash);
        $stmt->bindParam(":id", $usuario['id']);
        
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error: Erro ao redefinir senha.";
        }
    } else {
        echo "error: Token inválido ou expirado.";
    }
}
?>