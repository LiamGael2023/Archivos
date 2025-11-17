<?php

require_once __DIR__ . '/../../core/Model.php';

class File extends Model
{
    protected $table = 'files';

    /**
     * Obtener todos los archivos con ordenamiento
     */
    public function getAllOrdered($orderBy = 'name', $direction = 'ASC')
    {
        $allowedColumns = ['id', 'name', 'created_at', 'size', 'extension'];

        if (!in_array($orderBy, $allowedColumns)) {
            $orderBy = 'name';
        }

        if (!in_array(strtoupper($direction), ['ASC', 'DESC'])) {
            $direction = 'ASC';
        }

        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$direction}";
        return $this->query($sql);
    }

    /**
     * Obtener archivos de una carpeta específica
     */
    public function getByFolder($folderId, $orderBy = 'name', $direction = 'ASC')
    {
        $allowedColumns = ['id', 'name', 'created_at', 'size', 'extension'];

        if (!in_array($orderBy, $allowedColumns)) {
            $orderBy = 'name';
        }

        if (!in_array(strtoupper($direction), ['ASC', 'DESC'])) {
            $direction = 'ASC';
        }

        if ($folderId === null) {
            $sql = "SELECT * FROM {$this->table} WHERE folder_id IS NULL ORDER BY {$orderBy} {$direction}";
            return $this->query($sql);
        }

        $sql = "SELECT * FROM {$this->table} WHERE folder_id = ? ORDER BY {$orderBy} {$direction}";
        return $this->query($sql, [$folderId]);
    }

    /**
     * Crear un nuevo archivo
     */
    public function createFile($data)
    {
        $requiredFields = ['name', 'original_name', 'extension', 'size', 'mime_type', 'path'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new Exception("Campo requerido faltante: {$field}");
            }
        }

        return $this->create($data);
    }

    /**
     * Actualizar nombre de archivo
     */
    public function updateFileName($id, $newName)
    {
        return $this->update($id, ['name' => $newName]);
    }

    /**
     * Mover archivo a otra carpeta
     */
    public function moveToFolder($id, $folderId)
    {
        return $this->update($id, ['folder_id' => $folderId]);
    }

    /**
     * Buscar archivos por nombre o extensión
     */
    public function search($query, $searchType = 'name')
    {
        if ($searchType === 'extension') {
            $sql = "SELECT * FROM {$this->table} WHERE extension LIKE ? ORDER BY name ASC";
        } else {
            $sql = "SELECT * FROM {$this->table} WHERE name LIKE ? OR original_name LIKE ? ORDER BY name ASC";
            return $this->query($sql, ['%' . $query . '%', '%' . $query . '%']);
        }

        return $this->query($sql, ['%' . $query . '%']);
    }

    /**
     * Obtener archivos por extensión
     */
    public function getByExtension($extension)
    {
        return $this->where('extension', '=', $extension);
    }

    /**
     * Obtener estadísticas de archivos
     */
    public function getStats()
    {
        $sql = "SELECT
                    COUNT(*) as total_files,
                    SUM(size) as total_size,
                    AVG(size) as avg_size,
                    MAX(size) as max_size,
                    MIN(size) as min_size
                FROM {$this->table}";

        $result = $this->query($sql);
        return $result[0] ?? null;
    }

    /**
     * Obtener archivos por tipo MIME
     */
    public function getByMimeType($mimeType)
    {
        $sql = "SELECT * FROM {$this->table} WHERE mime_type LIKE ? ORDER BY name ASC";
        return $this->query($sql, [$mimeType . '%']);
    }

    /**
     * Obtener archivos recientes
     */
    public function getRecent($limit = 10)
    {
        $sql = "SELECT TOP (?) * FROM {$this->table} ORDER BY created_at DESC";
        return $this->query($sql, [$limit]);
    }

    /**
     * Obtener el tamaño total de archivos en una carpeta
     */
    public function getFolderSize($folderId)
    {
        $sql = "SELECT SUM(size) as total_size FROM {$this->table} WHERE folder_id = ?";
        $result = $this->query($sql, [$folderId]);

        return $result[0]['total_size'] ?? 0;
    }

    /**
     * Eliminar archivo físico y registro
     */
    public function deleteFile($id)
    {
        $file = $this->find($id);

        if (!$file) {
            return false;
        }

        // Eliminar archivo físico
        $fullPath = __DIR__ . '/../../public' . $file['path'];

        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        // Eliminar registro de base de datos
        return $this->delete($id);
    }

    /**
     * Obtener información completa del archivo con carpeta
     */
    public function getFileWithFolder($id)
    {
        $sql = "SELECT f.*, fo.name as folder_name, fo.path as folder_path
                FROM {$this->table} f
                LEFT JOIN folders fo ON f.folder_id = fo.id
                WHERE f.id = ?";

        $result = $this->query($sql, [$id]);
        return $result[0] ?? null;
    }
}
