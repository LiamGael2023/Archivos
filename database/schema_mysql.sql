-- =============================================
-- Sistema de Archivos - Base de Datos MySQL
-- MySQL/MariaDB Schema
-- =============================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS file_storage
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE file_storage;

-- =============================================
-- Tabla: folders (Carpetas)
-- =============================================
DROP TABLE IF EXISTS folders;

CREATE TABLE folders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    parent_id INT NULL,
    path TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_parent_id (parent_id),
    INDEX idx_path (path(255)),
    FOREIGN KEY (parent_id) REFERENCES folders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tabla: files (Archivos)
-- =============================================
DROP TABLE IF EXISTS files;

CREATE TABLE files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    extension VARCHAR(50) NOT NULL,
    size BIGINT NOT NULL COMMENT 'Tamaño en bytes',
    mime_type VARCHAR(100) NOT NULL,
    path TEXT NOT NULL COMMENT 'Ruta física del archivo',
    folder_id INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_folder_id (folder_id),
    INDEX idx_name (name),
    INDEX idx_extension (extension),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (folder_id) REFERENCES folders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tabla: metakeys (Etiquetas/Palabras clave)
-- =============================================
DROP TABLE IF EXISTS metakeys;

CREATE TABLE metakeys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(50) NOT NULL COMMENT 'file o folder',
    entity_id INT NOT NULL,
    key_name VARCHAR(100) NOT NULL,
    key_value VARCHAR(500) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_key_name (key_name),
    INDEX idx_key_value (key_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Tabla: shared_links (Enlaces compartidos)
-- =============================================
DROP TABLE IF EXISTS shared_links;

CREATE TABLE shared_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(50) NOT NULL COMMENT 'file o folder',
    entity_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    expires_at DATETIME NULL COMMENT 'NULL = no expira',
    download_count INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE INDEX idx_token (token),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Datos de prueba (opcional)
-- =============================================

-- Carpeta raíz
INSERT INTO folders (name, parent_id, path) VALUES ('Root', NULL, '/');
INSERT INTO folders (name, parent_id, path) VALUES ('Documentos', 1, '/Documentos');
INSERT INTO folders (name, parent_id, path) VALUES ('Imágenes', 1, '/Imágenes');
INSERT INTO folders (name, parent_id, path) VALUES ('Videos', 1, '/Videos');

-- Mensaje de confirmación
SELECT 'Schema MySQL creado exitosamente' AS mensaje;
