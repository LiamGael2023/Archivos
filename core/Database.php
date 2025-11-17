<?php

class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        $config = require_once __DIR__ . '/../config/database.php';

        try {
            // Construir DSN según el driver
            if ($config['driver'] === 'mysql') {
                $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
                if (isset($config['port'])) {
                    $dsn .= ";port={$config['port']}";
                }
            } elseif ($config['driver'] === 'sqlsrv') {
                $dsn = "sqlsrv:Server={$config['host']};Database={$config['database']}";
            } else {
                throw new Exception("Driver no soportado: {$config['driver']}");
            }

            $this->connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );

        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    // Prevenir clonación
    private function __clone() {}

    // Prevenir deserialización
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
