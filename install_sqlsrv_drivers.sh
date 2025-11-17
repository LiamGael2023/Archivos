#!/bin/bash
#
# Script de instalación de drivers SQL Server para PHP en Linux
# Ejecutar con: bash install_sqlsrv_drivers.sh
#

echo "================================================"
echo "  Instalador de Drivers SQL Server para PHP"
echo "================================================"
echo ""

# Detectar distribución de Linux
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS=$ID
    VER=$VERSION_ID
else
    echo "❌ No se pudo detectar la distribución de Linux"
    exit 1
fi

echo "📋 Sistema detectado: $OS $VER"
echo ""

# Detectar versión de PHP
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
echo "📋 PHP Version: $PHP_VERSION"
echo ""

# Función para Ubuntu/Debian
install_ubuntu_debian() {
    echo "🔧 Instalando para Ubuntu/Debian..."
    echo ""

    # Agregar repositorio de Microsoft
    echo "1️⃣ Agregando repositorio de Microsoft..."
    curl https://packages.microsoft.com/keys/microsoft.asc | sudo apt-key add -

    if [ "$VER" = "20.04" ] || [ "$VER" = "22.04" ]; then
        curl https://packages.microsoft.com/config/ubuntu/$VER/prod.list | sudo tee /etc/apt/sources.list.d/mssql-release.list
    else
        echo "⚠️  Versión de Ubuntu no reconocida, usando 20.04"
        curl https://packages.microsoft.com/config/ubuntu/20.04/prod.list | sudo tee /etc/apt/sources.list.d/mssql-release.list
    fi

    # Actualizar repositorios
    echo ""
    echo "2️⃣ Actualizando repositorios..."
    sudo apt-get update

    # Instalar ODBC Driver
    echo ""
    echo "3️⃣ Instalando Microsoft ODBC Driver 17..."
    sudo ACCEPT_EULA=Y apt-get install -y msodbcsql17

    # Instalar herramientas opcionales (sqlcmd)
    echo ""
    echo "4️⃣ Instalando herramientas de SQL Server..."
    sudo ACCEPT_EULA=Y apt-get install -y mssql-tools
    echo 'export PATH="$PATH:/opt/mssql-tools/bin"' >> ~/.bashrc
    source ~/.bashrc

    # Instalar unixODBC
    echo ""
    echo "5️⃣ Instalando unixODBC..."
    sudo apt-get install -y unixodbc-dev

    # Instalar extensiones PHP
    echo ""
    echo "6️⃣ Instalando extensiones PHP sqlsrv y pdo_sqlsrv..."
    sudo pecl install sqlsrv
    sudo pecl install pdo_sqlsrv

    # Agregar extensiones al php.ini
    echo ""
    echo "7️⃣ Configurando php.ini..."
    PHP_INI_DIR=$(php -i | grep "Scan this dir for additional .ini files" | cut -d ">" -f2 | xargs)

    if [ -z "$PHP_INI_DIR" ]; then
        PHP_INI_DIR="/etc/php/$PHP_VERSION/cli/conf.d"
    fi

    echo "extension=sqlsrv.so" | sudo tee $PHP_INI_DIR/30-sqlsrv.ini
    echo "extension=pdo_sqlsrv.so" | sudo tee $PHP_INI_DIR/30-pdo_sqlsrv.ini

    echo ""
    echo "✅ Instalación completada"
}

# Función para CentOS/RHEL
install_centos_rhel() {
    echo "🔧 Instalando para CentOS/RHEL..."
    echo ""

    echo "1️⃣ Agregando repositorio de Microsoft..."
    sudo curl https://packages.microsoft.com/config/rhel/8/prod.repo > /etc/yum.repos.d/mssql-release.repo

    echo ""
    echo "2️⃣ Instalando Microsoft ODBC Driver 17..."
    sudo ACCEPT_EULA=Y yum install -y msodbcsql17

    echo ""
    echo "3️⃣ Instalando herramientas de SQL Server..."
    sudo ACCEPT_EULA=Y yum install -y mssql-tools
    echo 'export PATH="$PATH:/opt/mssql-tools/bin"' >> ~/.bashrc
    source ~/.bashrc

    echo ""
    echo "4️⃣ Instalando unixODBC..."
    sudo yum install -y unixODBC-devel

    echo ""
    echo "5️⃣ Instalando extensiones PHP..."
    sudo pecl install sqlsrv
    sudo pecl install pdo_sqlsrv

    echo ""
    echo "6️⃣ Configurando php.ini..."
    echo "extension=sqlsrv.so" | sudo tee -a /etc/php.ini
    echo "extension=pdo_sqlsrv.so" | sudo tee -a /etc/php.ini

    echo ""
    echo "✅ Instalación completada"
}

# Ejecutar instalación según el sistema operativo
case $OS in
    ubuntu|debian)
        install_ubuntu_debian
        ;;
    centos|rhel)
        install_centos_rhel
        ;;
    *)
        echo "❌ Distribución no soportada: $OS"
        echo "   Instalación manual requerida"
        exit 1
        ;;
esac

# Verificar instalación
echo ""
echo "================================================"
echo "  Verificando instalación..."
echo "================================================"
echo ""

if php -m | grep -q sqlsrv && php -m | grep -q pdo_sqlsrv; then
    echo "✅ Drivers instalados correctamente:"
    php -m | grep -E '(sqlsrv|pdo_sqlsrv)'
    echo ""
    echo "🎉 ¡Todo listo para usar SQL Server con PHP!"
else
    echo "❌ Error: Los drivers no se instalaron correctamente"
    echo ""
    echo "Intenta instalar manualmente con:"
    echo "  sudo pecl install sqlsrv"
    echo "  sudo pecl install pdo_sqlsrv"
    exit 1
fi

echo ""
echo "📝 Próximos pasos:"
echo "  1. Asegúrate de que SQL Server esté ejecutándose"
echo "  2. Configura config/database.php con driver 'sqlsrv'"
echo "  3. Ejecuta: php install.php"
echo ""
