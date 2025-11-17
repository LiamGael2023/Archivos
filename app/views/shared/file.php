<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archivo Compartido - <?= htmlspecialchars($file['original_name']) ?></title>
    <link rel="stylesheet" href="/css/styles.css">
    <style>
        .shared-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .shared-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .shared-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .file-info {
            background: var(--bg-color);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .file-info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .file-info-row:last-child {
            border-bottom: none;
        }

        .file-info-label {
            font-weight: 600;
            color: var(--text-color);
        }

        .file-info-value {
            color: var(--text-light);
        }

        .download-section {
            text-align: center;
        }

        .download-btn {
            padding: 15px 40px;
            font-size: 18px;
        }

        .share-info {
            text-align: center;
            margin-top: 30px;
            color: var(--text-light);
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="shared-container">
        <div class="shared-header">
            <div class="shared-icon">📄</div>
            <h1><?= htmlspecialchars($file['original_name']) ?></h1>
            <p style="color: var(--text-light);">Archivo compartido</p>
        </div>

        <div class="file-info">
            <div class="file-info-row">
                <span class="file-info-label">Nombre:</span>
                <span class="file-info-value"><?= htmlspecialchars($file['original_name']) ?></span>
            </div>
            <div class="file-info-row">
                <span class="file-info-label">Tipo:</span>
                <span class="file-info-value"><?= strtoupper($file['extension']) ?> (<?= $file['mime_type'] ?>)</span>
            </div>
            <div class="file-info-row">
                <span class="file-info-label">Tamaño:</span>
                <span class="file-info-value"><?= formatFileSize($file['size']) ?></span>
            </div>
            <div class="file-info-row">
                <span class="file-info-label">Subido:</span>
                <span class="file-info-value"><?= date('d/m/Y H:i', strtotime($file['created_at'])) ?></span>
            </div>
        </div>

        <div class="download-section">
            <a href="/shared/<?= $token ?>/download" class="btn btn-primary download-btn">
                ⬇️ Descargar Archivo
            </a>
        </div>

        <div class="share-info">
            <p>Este archivo ha sido compartido contigo</p>
            <?php if ($link['expires_at']): ?>
                <p>Expira el: <?= date('d/m/Y H:i', strtotime($link['expires_at'])) ?></p>
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
?>
