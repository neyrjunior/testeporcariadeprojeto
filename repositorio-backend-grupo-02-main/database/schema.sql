CREATE DATABASE IF NOT EXISTS umhelp
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE umhelp;

CREATE TABLE IF NOT EXISTS usuarios (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome VARCHAR(60) NOT NULL,
    email VARCHAR(190) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    cpf CHAR(11) NOT NULL,
    data_nascimento DATE NOT NULL,
    cep VARCHAR(9) NOT NULL,
    endereco VARCHAR(150) NOT NULL,
    bairro VARCHAR(100) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    estado CHAR(2) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    perfil VARCHAR(20) NOT NULL DEFAULT 'comum',
    handle VARCHAR(80) NOT NULL,
    avatar VARCHAR(500) DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_email (email),
    UNIQUE KEY uq_usuarios_cpf (cpf),
    UNIQUE KEY uq_usuarios_handle (handle)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS relatos (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id BIGINT UNSIGNED NOT NULL,
    status ENUM('perdido', 'achado', 'recuperado') NOT NULL,
    local VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL,
    imagem VARCHAR(500) DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_relatos_usuario_criado (usuario_id, criado_em),
    KEY idx_relatos_status_criado (status, criado_em),
    CONSTRAINT fk_relatos_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mensagens (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    remetente_id BIGINT UNSIGNED NOT NULL,
    destinatario_id BIGINT UNSIGNED NOT NULL,
    relato_id BIGINT UNSIGNED DEFAULT NULL,
    mensagem TEXT NOT NULL,
    lida_em DATETIME DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_mensagens_remetente_destinatario (remetente_id, destinatario_id, criado_em),
    KEY idx_mensagens_destinatario_remetente (destinatario_id, remetente_id, criado_em),
    CONSTRAINT fk_mensagens_remetente
        FOREIGN KEY (remetente_id) REFERENCES usuarios (id) ON DELETE CASCADE,
    CONSTRAINT fk_mensagens_destinatario
        FOREIGN KEY (destinatario_id) REFERENCES usuarios (id) ON DELETE CASCADE,
    CONSTRAINT fk_mensagens_relato
        FOREIGN KEY (relato_id) REFERENCES relatos (id) ON DELETE SET NULL
) ENGINE=InnoDB;
