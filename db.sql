CREATE DATABASE sistema_login;
USE sistema_login;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    tentativas_login INT DEFAULT 0,
    bloqueado_ate DATETIME NULL,
    token_recuperacao VARCHAR(100) NULL,
    token_expiracao DATETIME NULL
);

CREATE TABLE logs_login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    data_login DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);