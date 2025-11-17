#!/bin/bash
# Script de instalación para XAMPP en Linux/Mac

echo "============================================"
echo "  Sistema de Archivos - Setup XAMPP"
echo "============================================"
echo ""

# Detectar ruta de XAMPP
if [ -d "/opt/lampp" ]; then
    XAMPP_PATH="/opt/lampp"
elif [ -d "/Applications/XAMPP" ]; then
    XAMPP_PATH="/Applications/XAMPP"
else
    echo "❌ Error: XAMPP no encontrado"
    echo "Por favor edita este archivo y establece XAMPP_PATH"
    exit 1
fi

echo "📋 XAMPP encontrado en: $XAMPP_PATH"
echo ""

# Verificar que MySQL esté corriendo
if ! $XAMPP_PATH/bin/mysql -u root -e "SELECT 1" > /dev/null 2>&1; then
    echo "❌ Error: MySQL no está corriendo"
    echo "Inicia MySQL desde el panel de XAMPP"
    exit 1
fi

echo "✅ MySQL está corriendo"
echo ""

echo "[1/4] Creando base de datos..."
$XAMPP_PATH/bin/mysql -u root -e "CREATE DATABASE IF NOT EXISTS file_storage CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
if [ $? -eq 0 ]; then
    echo "    ✅ Base de datos creada"
else
    echo "    ❌ Error al crear base de datos"
    exit 1
fi
echo ""

echo "[2/4] Importando schema..."
$XAMPP_PATH/bin/mysql -u root file_storage < database/schema_mysql.sql
if [ $? -eq 0 ]; then
    echo "    ✅ Schema importado"
else
    echo "    ❌ Error al importar schema"
    exit 1
fi
echo ""

echo "[3/4] Verificando permisos de uploads..."
mkdir -p public/uploads
chmod 755 public/uploads
echo "    ✅ Directorio uploads listo"
echo ""

echo "[4/4] Verificando configuración..."
if [ ! -f "config/database.php" ]; then
    echo "    Copiando configuración de ejemplo..."
    cp config/database_mysql.example.php config/database.php
fi
echo "    ✅ Configuración lista"
echo ""

echo "============================================"
echo "  ✅ Instalación completada!"
echo "============================================"
echo ""
echo "Próximos pasos:"
echo "1. Asegúrate de que Apache y MySQL estén corriendo en XAMPP"
echo "2. Copia este proyecto a: $XAMPP_PATH/htdocs/Archivos"
echo "3. Abre en tu navegador: http://localhost/Archivos/public/"
echo ""
