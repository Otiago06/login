<?php
class Database {
    private $host = "localhost";
    private $db_name = "sistema_login";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Configurar fuso horário do banco de dados
            $this->conn->exec("SET time_zone = '-03:00'"); // Fuso do Brasil
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

session_start();

// Configurar fuso horário do PHP
date_default_timezone_set('America/Sao_Paulo'); // Fuso do Brasil
?>