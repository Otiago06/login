<?php
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function generateRecoveryToken() {
    return bin2hex(random_bytes(16));
}

function logLogin($usuario_id, $db) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $query = "INSERT INTO logs_login (usuario_id, ip_address, user_agent) VALUES (:usuario_id, :ip, :user_agent)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":usuario_id", $usuario_id);
    $stmt->bindParam(":ip", $ip);
    $stmt->bindParam(":user_agent", $user_agent);
    $stmt->execute();
}

function formatarDataHora($dataHora, $formato = 'd/m/Y H:i') {
    if (empty($dataHora)) {
        return 'N/A';
    }
    
    try {
        $data = new DateTime($dataHora);
        return $data->format($formato);
    } catch (Exception $e) {
        return 'Data inválida';
    }
}

function getDataHoraAtual() {
    return date('Y-m-d H:i:s');
}

function adicionarMinutos($minutos) {
    $data = new DateTime();
    $data->modify("+{$minutos} minutes");
    return $data->format('Y-m-d H:i:s');
}
?>