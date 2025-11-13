<?php
include 'config.php';
include 'security.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        die("Token CSRF inválido");
    }
    
    $senha_atual = $_POST['senha_atual'];
    $nova_senha = $_POST['nova_senha'];
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Buscar usuário
    $query = "SELECT senha_hash FROM usuarios WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $_SESSION['usuario_id']);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (password_verify($senha_atual, $usuario['senha_hash'])) {
        // Senha atual correta, atualizar senha
        $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        
        $query = "UPDATE usuarios SET senha_hash = :senha_hash WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":senha_hash", $nova_senha_hash);
        $stmt->bindParam(":id", $_SESSION['usuario_id']);
        
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error: Erro ao atualizar senha.";
        }
    } else {
        echo "error: Senha atual incorreta.";
    }
}
?>