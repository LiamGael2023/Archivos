<?php

/**
 * Punto de entrada de la aplicación
 * Sistema de Archivos - File Storage
 */

// Configuración de errores (en producción cambiar a 0)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de zona horaria
date_default_timezone_set('America/Mexico_City');

// Autoload de clases core
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Router.php';

// Iniciar sesión (si se necesita autenticación en el futuro)
session_start();

// Crear instancia del router
$router = new Router();

// Cargar rutas
require_once __DIR__ . '/../routes/web.php';

// Ejecutar router
$router->dispatch();
