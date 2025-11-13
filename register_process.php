<?php
include 'config.php';
include 'security.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        die("Token CSRF inválido");
    }
    
    $nome = sanitizeInput($_POST['nome']);
    $email = sanitizeInput($_POST['email']);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    
    // Validações
    if (empty($nome) || empty($email) || empty($senha)) {
        echo "error: Todos os campos são obrigatórios.";
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "error: E-mail inválido.";
        exit;
    }
    
    if ($senha !== $confirmar_senha) {
        echo "error: As senhas não coincidem.";
        exit;
    }
    
    if (strlen($senha) < 6) {
        echo "error: A senha deve ter pelo menos 6 caracteres.";
        exit;
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Verificar se e-mail já existe
    $query = "SELECT id FROM usuarios WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "error: Este e-mail já está em uso.";
        exit;
    }
    
    // Criar usuário
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    
    $query = "INSERT INTO usuarios (nome, email, senha_hash) VALUES (:nome, :email, :senha_hash)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":nome", $nome);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":senha_hash", $senha_hash);
    
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error: Erro ao criar conta. Tente novamente.";
    }
}
?>