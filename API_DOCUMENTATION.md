# API Documentation - Sistema de Archivos

## Introducción

Esta API REST permite gestionar archivos, carpetas, metakeys y enlaces compartidos en el sistema de archivos.

Base URL: `http://tu-dominio.com/api`

## Formato de Respuesta

Todas las respuestas de la API están en formato JSON:

### Respuesta exitosa
```json
{
  "success": true,
  "message": "Operación exitosa",
  "data": { ... }
}
```

### Respuesta con error
```json
{
  "success": false,
  "message": "Descripción del error",
  "errors": { ... }
}
```

---

## Carpetas (Folders)

### Crear Carpeta

**Endpoint:** `POST /api/folders`

**Parámetros:**
| Nombre | Tipo | Requerido | Descripción |
|--------|------|-----------|-------------|
| name | string | Sí | Nombre de la carpeta |
| parent_id | integer | No | ID de la carpeta padre (NULL para raíz) |

**Ejemplo de request:**
```bash
curl -X POST http://tu-dominio.com/api/folders \
  -F "name=Mi Carpeta" \
  -F "parent_id=1"
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Carpeta creada exitosamente",
  "folder_id": 5
}
```

---

### Actualizar Carpeta

**Endpoint:** `PUT /api/folders/{id}`

**Parámetros:**
| Nombre | Tipo | Requerido | Descripción |
|--------|------|-----------|-------------|
| name | string | Sí | Nuevo nombre de la carpeta |

**Ejemplo de request:**
```bash
curl -X POST http://tu-dominio.com/api/folders/5 \
  -F "_method=PUT" \
  -F "name=Nuevo Nombre"
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Carpeta actualizada exitosamente"
}
```

---

### Eliminar Carpeta

**Endpoint:** `DELETE /api/folders/{id}`

**Ejemplo de request:**
```bash
curl -X DELETE http://tu-dominio.com/api/folders/5
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Carpeta eliminada exitosamente"
}
```

---

### Obtener Árbol de Carpetas

**Endpoint:** `GET /api/folders/tree`

**Respuesta:**
```json
{
  "success": true,
  "tree": [
    {
      "id": 1,
      "name": "Documentos",
      "level": 0,
      "children": [
        {
          "id": 2,
          "name": "Trabajo",
          "level": 1,
          "children": []
        }
      ]
    }
  ]
}
```

---

### Buscar Carpetas

**Endpoint:** `GET /api/folders/search`

**Query Parameters:**
| Nombre | Tipo | Descripción |
|--------|------|-------------|
| q | string | Término de búsqueda |

**Ejemplo de request:**
```bash
curl http://tu-dominio.com/api/folders/search?q=documentos
```

**Respuesta:**
```json
{
  "success": true,
  "results": [
    {
      "id": 1,
      "name": "Documentos",
      "path": "/Documentos",
      "created_at": "2024-01-15 10:30:00"
    }
  ]
}
```

---

## Archivos (Files)

### Subir Archivo

**Endpoint:** `POST /api/files/upload`

**Parámetros:**
| Nombre | Tipo | Requerido | Descripción |
|--------|------|-----------|-------------|
| file | file | Sí | Archivo a subir |
| folder_id | integer | No | ID de carpeta destino |

**Ejemplo de request:**
```bash
curl -X POST http://tu-dominio.com/api/files/upload \
  -F "file=@/ruta/al/archivo.pdf" \
  -F "folder_id=1"
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Archivo subido exitosamente",
  "file_id": 10,
  "file": {
    "id": 10,
    "name": "archivo",
    "original_name": "archivo.pdf",
    "extension": "pdf",
    "size": 1048576,
    "mime_type": "application/pdf",
    "path": "/uploads/2024/01/15/unique_name.pdf",
    "folder_id": 1
  }
}
```

---

### Actualizar Archivo

**Endpoint:** `PUT /api/files/{id}`

**Parámetros:**
| Nombre | Tipo | Requerido | Descripción |
|--------|------|-----------|-------------|
| name | string | Sí | Nuevo nombre del archivo |

**Ejemplo de request:**
```bash
curl -X POST http://tu-dominio.com/api/files/10 \
  -F "_method=PUT" \
  -F "name=nuevo_nombre"
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Archivo actualizado exitosamente"
}
```

---

### Mover Archivo

**Endpoint:** `POST /api/files/{id}/move`

**Parámetros:**
| Nombre | Tipo | Descripción |
|--------|------|-------------|
| folder_id | integer | ID de carpeta destino (NULL para raíz) |

**Ejemplo de request:**
```bash
curl -X POST http://tu-dominio.com/api/files/10/move \
  -F "folder_id=2"
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Archivo movido exitosamente"
}
```

---

### Eliminar Archivo

**Endpoint:** `DELETE /api/files/{id}`

**Ejemplo de request:**
```bash
curl -X DELETE http://tu-dominio.com/api/files/10
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Archivo eliminado exitosamente"
}
```

---

### Descargar Archivo

**Endpoint:** `GET /api/files/{id}/download`

**Ejemplo de request:**
```bash
curl -O http://tu-dominio.com/api/files/10/download
```

Retorna el archivo con headers apropiados para descarga.

---

### Buscar Archivos

**Endpoint:** `GET /api/files/search`

**Query Parameters:**
| Nombre | Tipo | Descripción |
|--------|------|-------------|
| q | string | Término de búsqueda |
| type | string | Tipo de búsqueda: 'name' o 'extension' |

**Ejemplo de request:**
```bash
curl http://tu-dominio.com/api/files/search?q=documento&type=name
```

**Respuesta:**
```json
{
  "success": true,
  "results": [
    {
      "id": 10,
      "name": "documento",
      "original_name": "documento.pdf",
      "extension": "pdf",
      "size": 1048576,
      "created_at": "2024-01-15 10:30:00"
    }
  ]
}
```

---

## MetaKeys (Etiquetas)

### Agregar MetaKey a Archivo

**Endpoint:** `POST /api/files/{id}/metakeys`

**Parámetros:**
| Nombre | Tipo | Requerido | Descripción |
|--------|------|-----------|-------------|
| key_name | string | Sí | Nombre de la clave |
| key_value | string | Sí | Valor de la clave |

**Ejemplo de request:**
```bash
curl -X POST http://tu-dominio.com/api/files/10/metakeys \
  -F "key_name=categoria" \
  -F "key_value=importante"
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Metakey agregada exitosamente",
  "metakey_id": 15
}
```

---

### Obtener MetaKeys de Archivo

**Endpoint:** `GET /api/files/{id}/metakeys`

**Respuesta:**
```json
{
  "success": true,
  "metakeys": [
    {
      "id": 15,
      "key_name": "categoria",
      "key_value": "importante",
      "created_at": "2024-01-15 10:30:00"
    }
  ]
}
```

---

### Agregar MetaKey a Carpeta

**Endpoint:** `POST /api/folders/{id}/metakeys`

Mismos parámetros y respuesta que archivos.

---

### Obtener MetaKeys de Carpeta

**Endpoint:** `GET /api/folders/{id}/metakeys`

Misma respuesta que archivos.

---

### Buscar por MetaKeys

**Endpoint:** `GET /api/files/search-by-metakeys`

**Query Parameters:**
| Nombre | Tipo | Requerido | Descripción |
|--------|------|-----------|-------------|
| key_name | string | Sí | Nombre de la clave |
| key_value | string | No | Valor de la clave (opcional) |

**Ejemplo de request:**
```bash
curl http://tu-dominio.com/api/files/search-by-metakeys?key_name=categoria&key_value=importante
```

**Respuesta:**
```json
{
  "success": true,
  "results": [
    {
      "id": 10,
      "name": "documento",
      "original_name": "documento.pdf",
      "extension": "pdf"
    }
  ]
}
```

---

## Enlaces Compartidos

### Crear Enlace para Archivo

**Endpoint:** `POST /api/files/{id}/share`

**Parámetros (opcionales):**
| Nombre | Tipo | Descripción |
|--------|------|-------------|
| expires_at | datetime | Fecha de expiración (formato: YYYY-MM-DD HH:MM:SS) |

**Ejemplo de request:**
```bash
curl -X POST http://tu-dominio.com/api/files/10/share \
  -F "expires_at=2024-12-31 23:59:59"
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Enlace creado exitosamente",
  "link": {
    "id": 20,
    "token": "a1b2c3d4e5f6g7h8i9j0",
    "is_active": 1,
    "expires_at": "2024-12-31 23:59:59",
    "download_count": 0
  },
  "public_url": "http://tu-dominio.com/shared/a1b2c3d4e5f6g7h8i9j0"
}
```

---

### Crear Enlace para Carpeta

**Endpoint:** `POST /api/folders/{id}/share`

Mismos parámetros y respuesta que archivos.

---

### Obtener Enlaces de Archivo

**Endpoint:** `GET /api/files/{id}/shares`

**Respuesta:**
```json
{
  "success": true,
  "links": [
    {
      "id": 20,
      "token": "a1b2c3d4e5f6g7h8i9j0",
      "is_active": 1,
      "expires_at": null,
      "download_count": 5,
      "created_at": "2024-01-15 10:30:00"
    }
  ]
}
```

---

### Obtener Enlaces de Carpeta

**Endpoint:** `GET /api/folders/{id}/shares`

Misma respuesta que archivos.

---

### Ver Contenido Compartido

**Endpoint:** `GET /shared/{token}`

Muestra una página web con el archivo o carpeta compartida.

---

### Descargar Archivo Compartido

**Endpoint:** `GET /shared/{token}/download`

Descarga directamente el archivo compartido.

---

### Desactivar Enlace

**Endpoint:** `POST /api/shared/{token}/deactivate`

**Respuesta:**
```json
{
  "success": true,
  "message": "Enlace desactivado exitosamente"
}
```

---

### Eliminar Enlace

**Endpoint:** `DELETE /api/shared/{token}`

**Respuesta:**
```json
{
  "success": true,
  "message": "Enlace eliminado exitosamente"
}
```

---

## Códigos de Estado HTTP

| Código | Descripción |
|--------|-------------|
| 200 | OK - Solicitud exitosa |
| 400 | Bad Request - Parámetros inválidos |
| 404 | Not Found - Recurso no encontrado |
| 500 | Internal Server Error - Error del servidor |

---

## Ejemplos de Uso

### Ejemplo: Crear carpeta y subir archivo

```bash
# 1. Crear carpeta
FOLDER_ID=$(curl -s -X POST http://localhost/api/folders \
  -F "name=Mis Documentos" | jq -r '.folder_id')

# 2. Subir archivo a la carpeta
FILE_ID=$(curl -s -X POST http://localhost/api/files/upload \
  -F "file=@documento.pdf" \
  -F "folder_id=$FOLDER_ID" | jq -r '.file_id')

# 3. Agregar metakey al archivo
curl -X POST http://localhost/api/files/$FILE_ID/metakeys \
  -F "key_name=categoria" \
  -F "key_value=importante"

# 4. Crear enlace compartido
curl -X POST http://localhost/api/files/$FILE_ID/share
```

### Ejemplo: Buscar archivos por metakey

```bash
# Buscar todos los archivos con categoria=importante
curl http://localhost/api/files/search-by-metakeys?key_name=categoria&key_value=importante
```

### Ejemplo: Obtener árbol completo de carpetas

```bash
curl http://localhost/api/folders/tree | jq '.'
```

---

## Notas Adicionales

- Todos los endpoints que modifican datos requieren método POST (usando `_method` para PUT/DELETE)
- Los archivos se organizan automáticamente por fecha en el directorio uploads
- Los tokens de enlaces compartidos son únicos y seguros
- Las metakeys permiten clasificación flexible de archivos y carpetas
- El sistema soporta cualquier tipo de archivo

## Soporte

Para más información, consulta el README.md o abre un issue en el repositorio.
