#!/usr/bin/env php
<?php
/**
 * Script de instalación del sistema de archivos
 * Ejecutar: php install.php
 */

echo "==============================================\n";
echo "  Sistema de Archivos - Instalador\n";
echo "==============================================\n\n";

// Verificar que existe el archivo de configuración
if (!file_exists(__DIR__ . '/config/database.php')) {
    echo "❌ Error: No existe config/database.php\n";
    echo "   Copia config/database.example.php o config/database_mysql.example.php\n";
    echo "   y renómbralo a config/database.php\n\n";
    exit(1);
}

// Cargar configuración
$config = require __DIR__ . '/config/database.php';

echo "📋 Configuración detectada:\n";
echo "   Driver: {$config['driver']}\n";
echo "   Host: {$config['host']}\n";
echo "   Database: {$config['database']}\n";
echo "   Username: {$config['username']}\n\n";

// Intentar conexión
echo "🔌 Probando conexión a base de datos...\n";

try {
    // Construir DSN
    if ($config['driver'] === 'mysql') {
        // Conectar sin especificar base de datos primero
        $dsn = "mysql:host={$config['host']};charset={$config['charset']}";
        if (isset($config['port'])) {
            $dsn .= ";port={$config['port']}";
        }
    } elseif ($config['driver'] === 'sqlsrv') {
        $dsn = "sqlsrv:Server={$config['host']}";
    } else {
        throw new Exception("Driver no soportado: {$config['driver']}");
    }

    $pdo = new PDO(
        $dsn,
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "✅ Conexión exitosa\n\n";

    // Crear base de datos si no existe
    echo "📦 Creando base de datos '{$config['database']}'...\n";

    if ($config['driver'] === 'mysql') {
        $pdo->exec("CREATE DATABASE IF NOT EXISTS {$config['database']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    } elseif ($config['driver'] === 'sqlsrv') {
        $pdo->exec("IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = '{$config['database']}') CREATE DATABASE {$config['database']}");
    }

    echo "✅ Base de datos creada/verificada\n\n";

    // Seleccionar base de datos
    if ($config['driver'] === 'mysql') {
        $pdo->exec("USE {$config['database']}");
    } else {
        $pdo->exec("USE {$config['database']}");
    }

    // Ejecutar schema
    echo "🗄️  Ejecutando schema...\n";

    $schemaFile = $config['driver'] === 'mysql'
        ? __DIR__ . '/database/schema_mysql.sql'
        : __DIR__ . '/database/schema.sql';

    if (!file_exists($schemaFile)) {
        throw new Exception("No se encontró el archivo de schema: $schemaFile");
    }

    $schema = file_get_contents($schemaFile);

    // Separar por punto y coma y ejecutar cada comando
    $statements = array_filter(
        array_map('trim', explode(';', $schema)),
        function($stmt) {
            return !empty($stmt) &&
                   !preg_match('/^(--|\/\*)/', $stmt) &&
                   !preg_match('/^(USE |CREATE DATABASE|DROP DATABASE)/i', $stmt);
        }
    );

    foreach ($statements as $statement) {
        if (!empty(trim($statement))) {
            try {
                $pdo->exec($statement);
            } catch (Exception $e) {
                // Ignorar errores de tablas que ya existen
                if (!str_contains($e->getMessage(), 'already exists')) {
                    echo "   ⚠️  Warning: " . $e->getMessage() . "\n";
                }
            }
        }
    }

    echo "✅ Schema ejecutado correctamente\n\n";

    // Verificar tablas creadas
    echo "📊 Verificando tablas...\n";

    if ($config['driver'] === 'mysql') {
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    } else {
        $tables = $pdo->query("SELECT name FROM sys.tables")->fetchAll(PDO::FETCH_COLUMN);
    }

    $expectedTables = ['folders', 'files', 'metakeys', 'shared_links'];
    $missingTables = array_diff($expectedTables, $tables);

    if (empty($missingTables)) {
        echo "✅ Todas las tablas creadas correctamente:\n";
        foreach ($expectedTables as $table) {
            echo "   - $table\n";
        }
    } else {
        echo "❌ Faltan tablas:\n";
        foreach ($missingTables as $table) {
            echo "   - $table\n";
        }
        exit(1);
    }

    // Verificar directorio de uploads
    echo "\n📁 Verificando directorio de uploads...\n";
    $uploadsDir = __DIR__ . '/public/uploads';

    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
        echo "✅ Directorio de uploads creado\n";
    } else {
        echo "✅ Directorio de uploads existe\n";
    }

    if (!is_writable($uploadsDir)) {
        echo "⚠️  Warning: El directorio de uploads no tiene permisos de escritura\n";
        echo "   Ejecuta: chmod -R 755 public/uploads\n";
    } else {
        echo "✅ Directorio de uploads tiene permisos correctos\n";
    }

    echo "\n==============================================\n";
    echo "  ✅ Instalación completada exitosamente\n";
    echo "==============================================\n\n";
    echo "🚀 Puedes acceder al sistema en:\n";
    echo "   http://localhost/\n\n";

} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n\n";
    echo "💡 Verifica:\n";
    echo "   1. Que el servidor de base de datos esté ejecutándose\n";
    echo "   2. Que las credenciales en config/database.php sean correctas\n";
    echo "   3. Que el driver PDO para {$config['driver']} esté instalado\n\n";

    // Mostrar drivers disponibles
    echo "📦 Drivers PDO disponibles:\n";
    $drivers = PDO::getAvailableDrivers();
    foreach ($drivers as $driver) {
        echo "   - $driver\n";
    }
    echo "\n";

    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}
