# Sistema de Archivos - File Storage

Sistema de gestión de archivos completo similar a OwnCloud, desarrollado en PHP con arquitectura MVC y SQL Server.

## 🚀 Características

- ✅ **Gestión de Carpetas**: Crear, renombrar, eliminar y navegar carpetas con estructura jerárquica
- ✅ **Gestión de Archivos**: Subir, descargar, renombrar y eliminar archivos de cualquier tipo
- ✅ **MetaKeys (Etiquetas)**: Sistema de etiquetas personalizadas para organizar archivos y carpetas
- ✅ **Árbol de Carpetas**: Visualización completa de la jerarquía de carpetas
- ✅ **Ordenamiento Flexible**: Ordena archivos por ID, nombre, fecha de creación o tamaño
- ✅ **Enlaces Compartidos**: Comparte archivos y carpetas a través de enlaces públicos
- ✅ **Búsqueda Avanzada**: Busca archivos por nombre, extensión o metakeys
- ✅ **Interfaz Moderna**: UI responsive y fácil de usar
- ✅ **API REST**: Endpoints completos para integración con otras aplicaciones

## 📋 Requisitos

- PHP 7.4 o superior
- SQL Server 2012 o superior
- Extensión PHP PDO con driver sqlsrv
- Servidor web (Apache o Nginx)
- mod_rewrite habilitado (para Apache)

## 🔧 Instalación

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio>
cd Archivos
```

### 2. Configurar la base de datos

**a) Crear la base de datos ejecutando el script SQL:**

```bash
sqlcmd -S localhost -U sa -P tu_password -i database/schema.sql
```

O ejecutar el archivo `database/schema.sql` en SQL Server Management Studio.

**b) Configurar la conexión:**

```bash
cp config/database.example.php config/database.php
```

Editar `config/database.php` con tus credenciales:

```php
return [
    'driver' => 'sqlsrv',
    'host' => 'localhost',        // Tu servidor SQL Server
    'database' => 'file_storage',
    'username' => 'sa',            // Tu usuario
    'password' => 'tu_password',   // Tu contraseña
    'charset' => 'UTF-8',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
```

### 3. Configurar permisos

```bash
chmod -R 755 public/uploads
```

### 4. Configurar servidor web

#### Apache

El proyecto incluye archivos `.htaccess` preconfigurados. Asegúrate de que `mod_rewrite` esté habilitado:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Configurar Virtual Host (opcional):

```apache
<VirtualHost *:80>
    ServerName file-storage.local
    DocumentRoot /ruta/al/proyecto/public

    <Directory /ruta/al/proyecto/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/file-storage-error.log
    CustomLog ${APACHE_LOG_DIR}/file-storage-access.log combined
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name file-storage.local;
    root /ruta/al/proyecto/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 5. Acceder a la aplicación

Abre tu navegador y accede a:
```
http://localhost/
```
o
```
http://file-storage.local/
```

## 📁 Estructura del Proyecto

```
Archivos/
├── app/
│   ├── controllers/      # Controladores MVC
│   │   ├── FileController.php
│   │   ├── FolderController.php
│   │   └── SharedController.php
│   ├── models/          # Modelos de datos
│   │   ├── File.php
│   │   ├── Folder.php
│   │   ├── MetaKey.php
│   │   └── SharedLink.php
│   └── views/           # Vistas HTML
│       ├── folders/
│       └── shared/
├── config/              # Configuración
│   └── database.php
├── core/                # Framework MVC
│   ├── Database.php
│   ├── Model.php
│   ├── Controller.php
│   └── Router.php
├── database/            # Scripts SQL
│   └── schema.sql
├── public/              # Archivos públicos
│   ├── css/
│   ├── js/
│   ├── uploads/        # Archivos subidos
│   └── index.php       # Punto de entrada
└── routes/              # Definición de rutas
    └── web.php
```

## 📖 Uso

### Gestión de Carpetas

1. **Crear carpeta**: Click en "Nueva Carpeta"
2. **Navegar**: Click en el nombre de la carpeta
3. **Renombrar**: Click en el icono de lápiz ✏️
4. **Eliminar**: Click en el icono de papelera 🗑️
5. **Compartir**: Click en el icono de enlace 🔗

### Gestión de Archivos

1. **Subir archivo**: Click en "Subir Archivo" y selecciona el archivo
2. **Descargar**: Click en el icono de descarga ⬇️
3. **Renombrar**: Click en el icono de lápiz ✏️
4. **Eliminar**: Click en el icono de papelera 🗑️
5. **Compartir**: Click en el icono de enlace 🔗

### Ordenamiento

Usa los controles de ordenamiento en la barra de herramientas para ordenar por:
- Nombre
- ID
- Fecha de creación
- Tamaño

Selecciona orden ascendente o descendente.

### Compartir Archivos/Carpetas

1. Click en el icono 🔗 del archivo o carpeta
2. Se generará un enlace único
3. Copia el enlace y compártelo
4. El enlace puede ser accedido públicamente sin autenticación

## 🔌 API REST

### Carpetas

```
GET    /                           # Vista principal
POST   /api/folders                # Crear carpeta
PUT    /api/folders/{id}           # Actualizar carpeta
DELETE /api/folders/{id}           # Eliminar carpeta
GET    /api/folders/tree           # Obtener árbol completo
GET    /api/folders/search?q=...   # Buscar carpetas
```

### Archivos

```
POST   /api/files/upload           # Subir archivo
PUT    /api/files/{id}             # Actualizar nombre
POST   /api/files/{id}/move        # Mover a otra carpeta
DELETE /api/files/{id}             # Eliminar archivo
GET    /api/files/{id}/download    # Descargar archivo
GET    /api/files/search?q=...     # Buscar archivos
```

### MetaKeys

```
POST   /api/files/{id}/metakeys       # Agregar metakey a archivo
GET    /api/files/{id}/metakeys       # Obtener metakeys de archivo
POST   /api/folders/{id}/metakeys     # Agregar metakey a carpeta
GET    /api/folders/{id}/metakeys     # Obtener metakeys de carpeta
GET    /api/files/search-by-metakeys  # Buscar por metakeys
```

### Enlaces Compartidos

```
POST   /api/files/{id}/share       # Crear enlace para archivo
GET    /api/files/{id}/shares       # Ver enlaces de archivo
POST   /api/folders/{id}/share     # Crear enlace para carpeta
GET    /api/folders/{id}/shares     # Ver enlaces de carpeta
GET    /shared/{token}              # Ver contenido compartido
GET    /shared/{token}/download     # Descargar archivo compartido
POST   /api/shared/{token}/deactivate  # Desactivar enlace
DELETE /api/shared/{token}          # Eliminar enlace
```

## 🗄️ Esquema de Base de Datos

### Tabla: folders
- `id` - ID autoincremental
- `name` - Nombre de la carpeta
- `parent_id` - ID de carpeta padre (NULL para raíz)
- `path` - Ruta completa
- `created_at` - Fecha de creación
- `updated_at` - Fecha de actualización

### Tabla: files
- `id` - ID autoincremental
- `name` - Nombre del archivo
- `original_name` - Nombre original
- `extension` - Extensión del archivo
- `size` - Tamaño en bytes
- `mime_type` - Tipo MIME
- `path` - Ruta física del archivo
- `folder_id` - ID de carpeta (NULL para raíz)
- `created_at` - Fecha de creación
- `updated_at` - Fecha de actualización

### Tabla: metakeys
- `id` - ID autoincremental
- `entity_type` - Tipo (file/folder)
- `entity_id` - ID de la entidad
- `key_name` - Nombre de la clave
- `key_value` - Valor de la clave
- `created_at` - Fecha de creación

### Tabla: shared_links
- `id` - ID autoincremental
- `entity_type` - Tipo (file/folder)
- `entity_id` - ID de la entidad
- `token` - Token único del enlace
- `is_active` - Estado del enlace
- `expires_at` - Fecha de expiración (NULL = no expira)
- `download_count` - Contador de descargas
- `created_at` - Fecha de creación
- `updated_at` - Fecha de actualización

## 🛠️ Desarrollo

### Agregar nuevas rutas

Edita `routes/web.php`:

```php
$router->get('mi-ruta/{id}', 'MiController@miMetodo');
```

### Crear nuevo controlador

```php
<?php
require_once __DIR__ . '/../../core/Controller.php';

class MiController extends Controller
{
    public function miMetodo($id)
    {
        // Tu lógica aquí
        $this->json(['success' => true]);
    }
}
```

### Crear nuevo modelo

```php
<?php
require_once __DIR__ . '/../../core/Model.php';

class MiModelo extends Model
{
    protected $table = 'mi_tabla';

    // Tus métodos aquí
}
```

## 🐛 Solución de Problemas

### Error de conexión a SQL Server

Verifica que el driver PDO sqlsrv esté instalado:
```bash
php -m | grep sqlsrv
```

Si no está instalado, instálalo:
```bash
# Ubuntu/Debian
sudo pecl install sqlsrv pdo_sqlsrv
```

### Archivos no se suben

Verifica permisos del directorio uploads:
```bash
chmod -R 755 public/uploads
chown -R www-data:www-data public/uploads
```

Verifica configuración de PHP (`php.ini`):
```ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
```

### Rutas no funcionan (404)

Asegúrate de que mod_rewrite esté habilitado en Apache:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## 📝 Licencia

Este proyecto es de código abierto.

## 👥 Autor

Desarrollado con ❤️ por el equipo de desarrollo

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📞 Soporte

Si tienes problemas o preguntas, abre un issue en GitHub.
