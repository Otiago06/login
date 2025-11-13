<?php
include 'config.php';
include 'security.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        die("Token CSRF inválido");
    }
    
    $email = sanitizeInput($_POST['email']);
    $senha = $_POST['senha'];
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Verificar se o usuário está bloqueado
    $query = "SELECT * FROM usuarios WHERE email = :email AND bloqueado_ate > NOW()";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        $tempoRestante = strtotime($usuario['bloqueado_ate']) - time();
        $minutosRestantes = ceil($tempoRestante / 60);
        
        echo "error: Conta temporariamente bloqueada. Tente novamente em {$minutosRestantes} minuto(s).";
        exit;
    }
    
    // Buscar usuário
    $query = "SELECT * FROM usuarios WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    
    if ($stmt->rowCount() == 1) {
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify($senha, $usuario['senha_hash'])) {
            // Login bem-sucedido
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['loggedin'] = true;
            
            // Resetar tentativas
            $query = "UPDATE usuarios SET tentativas_login = 0, bloqueado_ate = NULL WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":id", $usuario['id']);
            $stmt->execute();
            
            // Log do login
            logLogin($usuario['id'], $db);
            
            echo "success";
        } else {
            // Senha incorreta
            $tentativas = $usuario['tentativas_login'] + 1;
            
            if ($tentativas >= 3) {
                // Bloquear por 5 minutos
                $bloqueado_ate = adicionarMinutos(5);
                $query = "UPDATE usuarios SET tentativas_login = :tentativas, bloqueado_ate = :bloqueado_ate WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(":tentativas", $tentativas);
                $stmt->bindParam(":bloqueado_ate", $bloqueado_ate);
                $stmt->bindParam(":id", $usuario['id']);
                $stmt->execute();
                
                echo "error: Muitas tentativas falhas. Conta bloqueada por 5 minutos.";
            } else {
                $query = "UPDATE usuarios SET tentativas_login = :tentativas WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(":tentativas", $tentativas);
                $stmt->bindParam(":id", $usuario['id']);
                $stmt->execute();
                
                echo "error: Senha incorreta. Tentativas restantes: " . (3 - $tentativas);
            }
        }
    } else {
        echo "error: E-mail não encontrado.";
    }
}
?>