#!/usr/bin/env php
<?php
/**
 * Script de verificación del sistema
 * Ejecuta esto en TU SERVIDOR para ver qué está disponible
 */

echo "==============================================\n";
echo "  Verificación del Sistema de Archivos\n";
echo "==============================================\n\n";

// 1. Versión de PHP
echo "✓ PHP Version: " . PHP_VERSION . "\n\n";

// 2. Drivers PDO disponibles
echo "✓ Drivers PDO disponibles:\n";
$drivers = PDO::getAvailableDrivers();
if (empty($drivers)) {
    echo "  ❌ No hay drivers PDO instalados\n";
} else {
    foreach ($drivers as $driver) {
        echo "  - $driver\n";
    }
}
echo "\n";

// 3. Extensiones relevantes
echo "✓ Extensiones PHP relevantes:\n";
$extensions = ['pdo', 'pdo_mysql', 'pdo_sqlsrv', 'sqlsrv', 'mysqli'];
foreach ($extensions as $ext) {
    $status = extension_loaded($ext) ? '✅' : '❌';
    echo "  $status $ext\n";
}
echo "\n";

// 4. Verificar archivo de configuración
echo "✓ Configuración:\n";
if (file_exists(__DIR__ . '/config/database.php')) {
    $config = require __DIR__ . '/config/database.php';
    echo "  Driver configurado: {$config['driver']}\n";
    echo "  Host: {$config['host']}\n";
    echo "  Database: {$config['database']}\n";
    echo "  Username: {$config['username']}\n\n";

    // 5. Probar conexión
    echo "✓ Probando conexión a base de datos...\n";
    try {
        if ($config['driver'] === 'mysql') {
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

        echo "  ✅ Conexión exitosa!\n\n";

        // 6. Verificar base de datos
        if ($config['driver'] === 'mysql') {
            $databases = $pdo->query("SHOW DATABASES LIKE '{$config['database']}'")->fetchAll();
        } else {
            $databases = $pdo->query("SELECT name FROM sys.databases WHERE name = '{$config['database']}'")->fetchAll();
        }

        if (empty($databases)) {
            echo "  ⚠️  Base de datos '{$config['database']}' NO existe\n";
            echo "  💡 Ejecuta: php install.php\n\n";
        } else {
            echo "  ✅ Base de datos '{$config['database']}' existe\n\n";

            // Conectar a la base de datos
            $pdo->exec("USE {$config['database']}");

            // 7. Verificar tablas
            echo "✓ Verificando tablas:\n";
            if ($config['driver'] === 'mysql') {
                $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            } else {
                $tables = $pdo->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN);
            }

            $expectedTables = ['folders', 'files', 'metakeys', 'shared_links'];
            foreach ($expectedTables as $table) {
                $status = in_array($table, $tables) ? '✅' : '❌';
                echo "  $status $table\n";
            }

            if (count(array_intersect($expectedTables, $tables)) < 4) {
                echo "\n  ⚠️  Faltan tablas. Ejecuta: php install.php\n";
            }
        }

    } catch (PDOException $e) {
        echo "  ❌ Error de conexión: " . $e->getMessage() . "\n\n";
        echo "  💡 Verifica:\n";
        echo "    - Que el servidor de base de datos esté corriendo\n";
        echo "    - Que las credenciales sean correctas\n";
        if ($config['driver'] === 'mysql') {
            echo "    - Que MySQL esté corriendo: sudo systemctl start mysql\n";
        } else {
            echo "    - Que SQL Server esté corriendo: sudo systemctl start mssql-server\n";
        }
        echo "\n";
    } catch (Exception $e) {
        echo "  ❌ Error: " . $e->getMessage() . "\n\n";
    }
} else {
    echo "  ❌ No existe config/database.php\n";
    echo "  💡 Copia config/database.example.php o config/database_mysql.example.php\n\n";
}

// 8. Verificar directorio uploads
echo "✓ Directorio uploads:\n";
$uploadsDir = __DIR__ . '/public/uploads';
if (is_dir($uploadsDir)) {
    echo "  ✅ Existe\n";
    if (is_writable($uploadsDir)) {
        echo "  ✅ Tiene permisos de escritura\n";
    } else {
        echo "  ❌ NO tiene permisos de escritura\n";
        echo "  💡 Ejecuta: chmod -R 755 public/uploads\n";
    }
} else {
    echo "  ❌ No existe\n";
    echo "  💡 Ejecuta: mkdir -p public/uploads && chmod 755 public/uploads\n";
}

echo "\n==============================================\n";
echo "  Fin de la verificación\n";
echo "==============================================\n";
