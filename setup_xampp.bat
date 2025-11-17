@echo off
REM Script de instalación para XAMPP
REM Ejecutar como Administrador

echo ============================================
echo   Sistema de Archivos - Setup XAMPP
echo ============================================
echo.

REM Detectar ruta de XAMPP
set XAMPP_PATH=C:\xampp
if not exist "%XAMPP_PATH%" (
    echo Error: XAMPP no encontrado en %XAMPP_PATH%
    echo Por favor edita este archivo y cambia XAMPP_PATH
    pause
    exit /b 1
)

echo [1/4] Creando base de datos...
"%XAMPP_PATH%\mysql\bin\mysql" -u root -e "CREATE DATABASE IF NOT EXISTS file_storage CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
if errorlevel 1 (
    echo Error al crear base de datos
    echo Asegurate de que MySQL este corriendo en XAMPP
    pause
    exit /b 1
)
echo     OK - Base de datos creada

echo.
echo [2/4] Importando schema...
"%XAMPP_PATH%\mysql\bin\mysql" -u root file_storage < database\schema_mysql.sql
if errorlevel 1 (
    echo Error al importar schema
    pause
    exit /b 1
)
echo     OK - Schema importado

echo.
echo [3/4] Verificando permisos de uploads...
if not exist "public\uploads" mkdir "public\uploads"
echo     OK - Directorio uploads listo

echo.
echo [4/4] Verificando configuracion...
if not exist "config\database.php" (
    echo Copiando configuracion de ejemplo...
    copy "config\database_mysql.example.php" "config\database.php"
)
echo     OK - Configuracion lista

echo.
echo ============================================
echo   Instalacion completada!
echo ============================================
echo.
echo Proximos pasos:
echo 1. Asegurate de que Apache y MySQL esten corriendo en XAMPP
echo 2. Copia este proyecto a: %XAMPP_PATH%\htdocs\Archivos
echo 3. Abre en tu navegador: http://localhost/Archivos/public/
echo.
pause
