<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carpeta Compartida - <?= htmlspecialchars($folder['name']) ?></title>
    <link rel="stylesheet" href="/css/styles.css">
    <style>
        .shared-header {
            background: white;
            padding: 30px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .shared-header h1 {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="shared-header">
            <h1>
                <span style="font-size: 48px;">📁</span>
                <?= htmlspecialchars($folder['name']) ?>
            </h1>
            <p style="color: var(--text-light);">Carpeta compartida</p>

            <?php if (isset($breadcrumb) && count($breadcrumb) > 1): ?>
            <div style="margin-top: 15px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                <?php foreach ($breadcrumb as $index => $crumb): ?>
                    <?php if ($index > 0): ?>
                        <span style="color: var(--text-light);">›</span>
                    <?php endif; ?>
                    <?php if ($index < count($breadcrumb) - 1): ?>
                        <a href="/shared/<?= $token ?><?= $index === 0 ? '' : '/folder/' . $crumb['id'] ?>"
                           style="color: var(--primary-color); text-decoration: none;">
                            <?= htmlspecialchars($crumb['name']) ?>
                        </a>
                    <?php else: ?>
                        <span style="font-weight: 600;">
                            <?= htmlspecialchars($crumb['name']) ?>
                        </span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- File Grid -->
        <div class="file-grid">
            <div class="file-grid-header">
                <div></div>
                <div>Nombre</div>
                <div>Tipo</div>
                <div>Tamaño</div>
                <div>Fecha</div>
                <div>Acciones</div>
            </div>

            <?php if (empty($subfolders) && empty($files)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📂</div>
                    <h3>Esta carpeta está vacía</h3>
                </div>
            <?php else: ?>
                <!-- Subcarpetas -->
                <?php foreach ($subfolders as $subfolder): ?>
                <div class="file-grid-row" style="cursor: pointer;" onclick="window.location.href='/shared/<?= $token ?>/folder/<?= $subfolder['id'] ?>'">
                    <div class="file-icon folder-icon">📁</div>
                    <div class="file-name">
                        <a href="/shared/<?= $token ?>/folder/<?= $subfolder['id'] ?>" style="text-decoration: none; color: inherit;">
                            <?= htmlspecialchars($subfolder['name']) ?>
                        </a>
                    </div>
                    <div class="file-type">Carpeta</div>
                    <div class="file-size">-</div>
                    <div class="file-date"><?= date('d/m/Y H:i', strtotime($subfolder['created_at'])) ?></div>
                    <div class="file-actions">
                        <a href="/shared/<?= $token ?>/folder/<?= $subfolder['id'] ?>" class="action-btn" title="Abrir">
                            📂 Abrir
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Archivos -->
                <?php foreach ($files as $file): ?>
                <div class="file-grid-row">
                    <div class="file-icon"><?= getFileIcon($file['extension']) ?></div>
                    <div class="file-name">
                        <?= htmlspecialchars($file['name']) ?>.<?= htmlspecialchars($file['extension']) ?>
                    </div>
                    <div class="file-type"><?= strtoupper($file['extension']) ?></div>
                    <div class="file-size"><?= formatFileSize($file['size']) ?></div>
                    <div class="file-date"><?= date('d/m/Y H:i', strtotime($file['created_at'])) ?></div>
                    <div class="file-actions">
                        <a href="/api/files/<?= $file['id'] ?>/download" class="action-btn" title="Descargar">
                            ⬇️ Descargar
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
function formatFileSize($bytes) {
    if ($bytes === 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

function getFileIcon($extension) {
    $icons = [
        'pdf' => '📄', 'doc' => '📄', 'docx' => '📄',
        'xls' => '📊', 'xlsx' => '📊',
        'jpg' => '🖼️', 'jpeg' => '🖼️', 'png' => '🖼️', 'gif' => '🖼️',
        'mp4' => '🎬', 'avi' => '🎬',
        'mp3' => '🎵', 'wav' => '🎵',
        'zip' => '📦', 'rar' => '📦',
        'txt' => '📝'
    ];
    return $icons[strtolower($extension)] ?? '📄';
}
?>
