<?php

return [
    'driver' => 'sqlsrv',
    'host' => 'localhost',
    'database' => 'file_storage',
    'username' => 'sa',
    'password' => 'your_password',
    'charset' => 'UTF-8',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
