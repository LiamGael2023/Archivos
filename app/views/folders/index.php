<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Archivos - File Storage</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <h1>
                <span>📁</span>
                Sistema de Archivos
            </h1>
        </div>
    </div>

    <div class="container">
        <!-- Breadcrumb -->
        <?php if (!empty($breadcrumb)): ?>
        <div class="breadcrumb">
            <a href="/" class="breadcrumb-item">
                🏠 Inicio
            </a>
            <?php foreach ($breadcrumb as $item): ?>
                <span class="breadcrumb-separator">/</span>
                <a href="/?folder=<?= $item['id'] ?>" class="breadcrumb-item <?= $item['id'] == $currentFolder['id'] ? 'active' : '' ?>">
                    <?= htmlspecialchars($item['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Toolbar -->
        <div class="toolbar">
            <div class="toolbar-left">
                <button class="btn btn-primary" id="createFolderBtn">
                    📁 Nueva Carpeta
                </button>
                <button class="btn btn-success" id="uploadFileBtn">
                    ⬆️ Subir Archivo
                </button>
            </div>

            <div class="toolbar-right">
                <div class="sort-controls">
                    <label>Ordenar por:</label>
                    <select id="sortBy">
                        <option value="name" <?= $orderBy === 'name' ? 'selected' : '' ?>>Nombre</option>
                        <option value="id" <?= $orderBy === 'id' ? 'selected' : '' ?>>ID</option>
                        <option value="created_at" <?= $orderBy === 'created_at' ? 'selected' : '' ?>>Fecha</option>
                        <option value="size" <?= $orderBy === 'size' ? 'selected' : '' ?>>Tamaño</option>
                    </select>

                    <select id="direction">
                        <option value="ASC" <?= $direction === 'ASC' ? 'selected' : '' ?>>↑ Ascendente</option>
                        <option value="DESC" <?= $direction === 'DESC' ? 'selected' : '' ?>>↓ Descendente</option>
                    </select>
                </div>

                <input type="text" class="search-input" id="searchInput" placeholder="🔍 Buscar archivos...">
            </div>
        </div>

        <!-- File Grid -->
        <div class="file-grid">
            <!-- Header -->
            <div class="file-grid-header">
                <div></div>
                <div>Nombre</div>
                <div>Tipo</div>
                <div>Tamaño</div>
                <div>Fecha</div>
                <div>Acciones</div>
            </div>

            <!-- Folders -->
            <?php if (empty($folders) && empty($files)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📂</div>
                    <h3>Esta carpeta está vacía</h3>
                    <p>Crea una nueva carpeta o sube archivos para comenzar</p>
                </div>
            <?php else: ?>
                <?php foreach ($folders as $folder): ?>
                <div class="file-grid-row">
                    <div class="file-icon folder-icon">📁</div>
                    <div class="file-name">
                        <a href="/?folder=<?= $folder['id'] ?>">
                            <?= htmlspecialchars($folder['name']) ?>
                        </a>
                    </div>
                    <div class="file-type">Carpeta</div>
                    <div class="file-size">-</div>
                    <div class="file-date"><?= date('d/m/Y H:i', strtotime($folder['created_at'])) ?></div>
                    <div class="file-actions">
                        <button class="action-btn" onclick="fileStorage.renameFolder(<?= $folder['id'] ?>)" title="Renombrar">
                            ✏️
                        </button>
                        <button class="action-btn" onclick="fileStorage.shareFolder(<?= $folder['id'] ?>)" title="Compartir">
                            🔗
                        </button>
                        <button class="action-btn" onclick="fileStorage.deleteFolder(<?= $folder['id'] ?>, '<?= addslashes($folder['name']) ?>')" title="Eliminar">
                            🗑️
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Files -->
                <?php foreach ($files as $file): ?>
                <div class="file-grid-row">
                    <div class="file-icon <?= $this->getFileIconClass($file['extension']) ?>">
                        <?= $this->getFileIcon($file['extension']) ?>
                    </div>
                    <div class="file-name">
                        <?= htmlspecialchars($file['name']) ?>.<?= htmlspecialchars($file['extension']) ?>
                    </div>
                    <div class="file-type"><?= strtoupper($file['extension']) ?></div>
                    <div class="file-size"><?= $this->formatFileSize($file['size']) ?></div>
                    <div class="file-date"><?= date('d/m/Y H:i', strtotime($file['created_at'])) ?></div>
                    <div class="file-actions">
                        <a href="/api/files/<?= $file['id'] ?>/download" class="action-btn" title="Descargar">
                            ⬇️
                        </a>
                        <button class="action-btn" onclick="fileStorage.renameFile(<?= $file['id'] ?>)" title="Renombrar">
                            ✏️
                        </button>
                        <button class="action-btn" onclick="fileStorage.shareFile(<?= $file['id'] ?>)" title="Compartir">
                            🔗
                        </button>
                        <button class="action-btn" onclick="fileStorage.deleteFile(<?= $file['id'] ?>, '<?= addslashes($file['original_name']) ?>')" title="Eliminar">
                            🗑️
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal: Crear Carpeta -->
    <div id="createFolderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📁 Nueva Carpeta</h2>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="folderNameInput">Nombre de la carpeta:</label>
                    <input type="text" id="folderNameInput" placeholder="Ingresa el nombre...">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="fileStorage.hideCreateFolderModal()">Cancelar</button>
                <button class="btn btn-primary" onclick="fileStorage.createFolder()">Crear</button>
            </div>
        </div>
    </div>

    <!-- Modal: Subir Archivo -->
    <div id="uploadFileModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>⬆️ Subir Archivo</h2>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="fileInput">Selecciona uno o más archivos:</label>
                    <input type="file" id="fileInput" multiple>
                    <small style="color: var(--text-light); display: block; margin-top: 5px;">
                        Puedes seleccionar múltiples archivos manteniendo presionada la tecla Ctrl (Cmd en Mac)
                    </small>
                </div>
                <div id="filePreview" style="margin-top: 15px;"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="fileStorage.hideUploadFileModal()">Cancelar</button>
                <button class="btn btn-success" onclick="fileStorage.uploadFile()">Subir</button>
            </div>
        </div>
    </div>

    <!-- Modal: Enlace Compartido -->
    <div id="shareLinkModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>🔗 Enlace Compartido</h2>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="shareLinkInput">Copia este enlace para compartir:</label>
                    <input type="text" id="shareLinkInput" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="fileStorage.copyShareLink()">📋 Copiar</button>
                <button class="btn btn-secondary" onclick="fileStorage.hideShareLinkModal()">Cerrar</button>
            </div>
        </div>
    </div>

    <script src="/js/app.js"></script>
    <script>
        // Configurar ID de carpeta actual
        <?php if ($currentFolder): ?>
        fileStorage.currentFolderId = <?= $currentFolder['id'] ?>;
        <?php endif; ?>

        // Cerrar modales al hacer click fuera
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>

<?php
// Helper functions para la vista
class ViewHelpers {
    public static function getFileIcon($extension) {
        $icons = [
            'pdf' => '📄',
            'doc' => '📄',
            'docx' => '📄',
            'xls' => '📊',
            'xlsx' => '📊',
            'ppt' => '📊',
            'pptx' => '📊',
            'jpg' => '🖼️',
            'jpeg' => '🖼️',
            'png' => '🖼️',
            'gif' => '🖼️',
            'svg' => '🖼️',
            'mp4' => '🎬',
            'avi' => '🎬',
            'mov' => '🎬',
            'mp3' => '🎵',
            'wav' => '🎵',
            'zip' => '📦',
            'rar' => '📦',
            'txt' => '📝',
            'html' => '🌐',
            'css' => '🎨',
            'js' => '⚙️',
            'php' => '⚙️',
        ];

        return $icons[strtolower($extension)] ?? '📄';
    }

    public static function getFileIconClass($extension) {
        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
        $videoExts = ['mp4', 'avi', 'mov'];
        $docExts = ['pdf', 'doc', 'docx', 'txt'];

        $ext = strtolower($extension);

        if (in_array($ext, $imageExts)) return 'file-icon-img';
        if (in_array($ext, $videoExts)) return 'file-icon-video';
        if (in_array($ext, $docExts)) return 'file-icon-doc';

        return 'file-icon-default';
    }

    public static function formatFileSize($bytes) {
        if ($bytes === 0) return '0 Bytes';

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
}

// Hacer helpers accesibles en la vista
$helpers = new ViewHelpers();
foreach (get_class_methods($helpers) as $method) {
    if (!function_exists($method)) {
        eval("function $method(...\$args) { return ViewHelpers::$method(...\$args); }");
    }
}
?>
