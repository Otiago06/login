<?php
include 'config.php';
include 'security.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        die("Token CSRF inválido");
    }
    
    $email = sanitizeInput($_POST['email']);
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Verificar se o e-mail existe
    $query = "SELECT id, nome FROM usuarios WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    
    if ($stmt->rowCount() == 1) {
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Gerar token de recuperação
        $token = generateRecoveryToken();
        $expiracao = adicionarMinutos(60); // Expira em 1 hora
        
        // Salvar token no banco
        $query = "UPDATE usuarios SET token_recuperacao = :token, token_expiracao = :expiracao WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":token", $token);
        $stmt->bindParam(":expiracao", $expiracao);
        $stmt->bindParam(":id", $usuario['id']);
        $stmt->execute();
        
        // Aqui você implementaria o envio de e-mail
        $assunto = "Recuperação de Senha - Sistema";
        $mensagem = "Olá " . $usuario['nome'] . ",\n\n";
        $mensagem .= "Você solicitou a recuperação de senha em " . formatarDataHora(getDataHoraAtual()) . ".\n";
        $mensagem .= "Clique no link abaixo para redefinir sua senha:\n";
        $mensagem .= "http://seusite.com/reset_password.php?token=" . $token . "\n\n";
        $mensagem .= "Este link expira em " . formatarDataHora($expiracao) . ".\n\n";
        $mensagem .= "Se você não solicitou esta recuperação, ignore este e-mail.";
        
        $headers = "From: sistema@seusite.com\r\n";
        $headers .= "Content-Type: text/plain; charset=utf-8";
        
        // Em produção, descomente a linha abaixo e configure seu servidor SMTP
        // mail($email, $assunto, $mensagem, $headers);
        
        // Para demonstração
        echo "success: Link de recuperação gerado (Token: " . $token . " - Expira: " . formatarDataHora($expiracao) . ")";
        
    } else {
        echo "error: E-mail não encontrado.";
    }
}
?>