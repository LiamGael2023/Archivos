-- =============================================
-- Sistema de Archivos - Base de Datos
-- SQL Server Schema
-- =============================================

-- Crear base de datos
IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = 'file_storage')
BEGIN
    CREATE DATABASE file_storage;
END
GO

USE file_storage;
GO

-- =============================================
-- Tabla: folders (Carpetas)
-- =============================================
IF OBJECT_ID('folders', 'U') IS NOT NULL
    DROP TABLE folders;
GO

CREATE TABLE folders (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name NVARCHAR(255) NOT NULL,
    parent_id INT NULL,
    path NVARCHAR(MAX) NOT NULL,
    created_at DATETIME2 DEFAULT GETDATE(),
    updated_at DATETIME2 DEFAULT GETDATE(),
    FOREIGN KEY (parent_id) REFERENCES folders(id) ON DELETE NO ACTION
);
GO

-- Índices para folders
CREATE INDEX idx_folders_parent_id ON folders(parent_id);
CREATE INDEX idx_folders_path ON folders(path);
GO

-- =============================================
-- Tabla: files (Archivos)
-- =============================================
IF OBJECT_ID('files', 'U') IS NOT NULL
    DROP TABLE files;
GO

CREATE TABLE files (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name NVARCHAR(255) NOT NULL,
    original_name NVARCHAR(255) NOT NULL,
    extension NVARCHAR(50) NOT NULL,
    size BIGINT NOT NULL, -- Tamaño en bytes
    mime_type NVARCHAR(100) NOT NULL,
    path NVARCHAR(MAX) NOT NULL, -- Ruta física del archivo
    folder_id INT NULL,
    created_at DATETIME2 DEFAULT GETDATE(),
    updated_at DATETIME2 DEFAULT GETDATE(),
    FOREIGN KEY (folder_id) REFERENCES folders(id) ON DELETE CASCADE
);
GO

-- Índices para files
CREATE INDEX idx_files_folder_id ON files(folder_id);
CREATE INDEX idx_files_name ON files(name);
CREATE INDEX idx_files_extension ON files(extension);
CREATE INDEX idx_files_created_at ON files(created_at);
GO

-- =============================================
-- Tabla: metakeys (Etiquetas/Palabras clave)
-- =============================================
IF OBJECT_ID('metakeys', 'U') IS NOT NULL
    DROP TABLE metakeys;
GO

CREATE TABLE metakeys (
    id INT IDENTITY(1,1) PRIMARY KEY,
    entity_type NVARCHAR(50) NOT NULL, -- 'file' o 'folder'
    entity_id INT NOT NULL,
    key_name NVARCHAR(100) NOT NULL,
    key_value NVARCHAR(500) NOT NULL,
    created_at DATETIME2 DEFAULT GETDATE()
);
GO

-- Índices para metakeys
CREATE INDEX idx_metakeys_entity ON metakeys(entity_type, entity_id);
CREATE INDEX idx_metakeys_key_name ON metakeys(key_name);
CREATE INDEX idx_metakeys_key_value ON metakeys(key_value);
GO

-- =============================================
-- Tabla: shared_links (Enlaces compartidos)
-- =============================================
IF OBJECT_ID('shared_links', 'U') IS NOT NULL
    DROP TABLE shared_links;
GO

CREATE TABLE shared_links (
    id INT IDENTITY(1,1) PRIMARY KEY,
    entity_type NVARCHAR(50) NOT NULL, -- 'file' o 'folder'
    entity_id INT NOT NULL,
    token NVARCHAR(255) UNIQUE NOT NULL,
    is_active BIT DEFAULT 1,
    expires_at DATETIME2 NULL, -- NULL = no expira
    download_count INT DEFAULT 0,
    created_at DATETIME2 DEFAULT GETDATE(),
    updated_at DATETIME2 DEFAULT GETDATE()
);
GO

-- Índices para shared_links
CREATE UNIQUE INDEX idx_shared_links_token ON shared_links(token);
CREATE INDEX idx_shared_links_entity ON shared_links(entity_type, entity_id);
CREATE INDEX idx_shared_links_active ON shared_links(is_active);
GO

-- =============================================
-- Trigger para actualizar updated_at
-- =============================================

-- Trigger para folders
IF OBJECT_ID('tr_folders_updated_at', 'TR') IS NOT NULL
    DROP TRIGGER tr_folders_updated_at;
GO

CREATE TRIGGER tr_folders_updated_at
ON folders
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE folders
    SET updated_at = GETDATE()
    FROM folders f
    INNER JOIN inserted i ON f.id = i.id;
END
GO

-- Trigger para files
IF OBJECT_ID('tr_files_updated_at', 'TR') IS NOT NULL
    DROP TRIGGER tr_files_updated_at;
GO

CREATE TRIGGER tr_files_updated_at
ON files
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE files
    SET updated_at = GETDATE()
    FROM files f
    INNER JOIN inserted i ON f.id = i.id;
END
GO

-- Trigger para shared_links
IF OBJECT_ID('tr_shared_links_updated_at', 'TR') IS NOT NULL
    DROP TRIGGER tr_shared_links_updated_at;
GO

CREATE TRIGGER tr_shared_links_updated_at
ON shared_links
AFTER UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    UPDATE shared_links
    SET updated_at = GETDATE()
    FROM shared_links sl
    INNER JOIN inserted i ON sl.id = i.id;
END
GO

-- =============================================
-- Datos de prueba (opcional)
-- =============================================

-- Carpeta raíz
INSERT INTO folders (name, parent_id, path) VALUES ('Root', NULL, '/');
INSERT INTO folders (name, parent_id, path) VALUES ('Documentos', 1, '/Documentos');
INSERT INTO folders (name, parent_id, path) VALUES ('Imágenes', 1, '/Imágenes');
INSERT INTO folders (name, parent_id, path) VALUES ('Videos', 1, '/Videos');

GO

PRINT 'Schema creado exitosamente';
