<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enlace Inválido</title>
    <link rel="stylesheet" href="/css/styles.css">
    <style>
        .error-container {
            max-width: 600px;
            margin: 100px auto;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .error-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .error-title {
            font-size: 24px;
            color: var(--danger-color);
            margin-bottom: 15px;
        }

        .error-message {
            color: var(--text-light);
            font-size: 16px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">❌</div>
        <h1 class="error-title">Enlace no válido</h1>
        <p class="error-message">
            <?= htmlspecialchars($message ?? 'Este enlace no es válido o ha expirado.') ?>
        </p>
    </div>
</body>
</html>
