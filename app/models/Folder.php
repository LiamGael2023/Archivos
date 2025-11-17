<?php

require_once __DIR__ . '/../../core/Model.php';

class Folder extends Model
{
    protected $table = 'folders';

    /**
     * Obtener todas las carpetas ordenadas
     */
    public function getAllOrdered($orderBy = 'name', $direction = 'ASC')
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$direction}";
        return $this->query($sql);
    }

    /**
     * Obtener carpetas hijas de una carpeta padre
     */
    public function getChildren($parentId = null)
    {
        if ($parentId === null) {
            $sql = "SELECT * FROM {$this->table} WHERE parent_id IS NULL ORDER BY name ASC";
            return $this->query($sql);
        }

        return $this->where('parent_id', '=', $parentId);
    }

    /**
     * Obtener el árbol completo de carpetas
     */
    public function getTree($parentId = null, $level = 0)
    {
        $folders = $this->getChildren($parentId);
        $tree = [];

        foreach ($folders as $folder) {
            $folder['level'] = $level;
            $folder['children'] = $this->getTree($folder['id'], $level + 1);
            $tree[] = $folder;
        }

        return $tree;
    }

    /**
     * Obtener el path completo de una carpeta
     */
    public function getFullPath($folderId)
    {
        $folder = $this->find($folderId);

        if (!$folder) {
            return null;
        }

        if ($folder['parent_id'] === null) {
            return $folder['name'];
        }

        $parentPath = $this->getFullPath($folder['parent_id']);
        return $parentPath . '/' . $folder['name'];
    }

    /**
     * Crear una nueva carpeta
     */
    public function createFolder($name, $parentId = null)
    {
        // Construir el path
        if ($parentId === null) {
            $path = '/' . $name;
        } else {
            $parentPath = $this->getFullPath($parentId);
            $path = $parentPath . '/' . $name;
        }

        $data = [
            'name' => $name,
            'parent_id' => $parentId,
            'path' => $path
        ];

        return $this->create($data);
    }

    /**
     * Actualizar nombre de carpeta y actualizar paths de hijos
     */
    public function updateFolder($id, $name)
    {
        $folder = $this->find($id);

        if (!$folder) {
            return false;
        }

        // Actualizar el path
        $oldPath = $folder['path'];

        if ($folder['parent_id'] === null) {
            $newPath = '/' . $name;
        } else {
            $parentPath = $this->getFullPath($folder['parent_id']);
            $newPath = $parentPath . '/' . $name;
        }

        // Actualizar carpeta actual
        $this->update($id, [
            'name' => $name,
            'path' => $newPath
        ]);

        // Actualizar paths de carpetas hijas recursivamente
        $this->updateChildrenPaths($oldPath, $newPath);

        return true;
    }

    /**
     * Actualizar paths de carpetas hijas
     */
    private function updateChildrenPaths($oldPath, $newPath)
    {
        $sql = "UPDATE {$this->table}
                SET path = REPLACE(path, ?, ?)
                WHERE path LIKE ?";

        $this->execute($sql, [$oldPath, $newPath, $oldPath . '/%']);
    }

    /**
     * Eliminar carpeta y sus hijos (cascada)
     */
    public function deleteFolder($id)
    {
        // SQL Server manejará la cascada automáticamente
        return $this->delete($id);
    }

    /**
     * Obtener breadcrumb (ruta de navegación)
     */
    public function getBreadcrumb($folderId)
    {
        $breadcrumb = [];
        $currentId = $folderId;

        while ($currentId !== null) {
            $folder = $this->find($currentId);

            if (!$folder) {
                break;
            }

            array_unshift($breadcrumb, $folder);
            $currentId = $folder['parent_id'];
        }

        return $breadcrumb;
    }

    /**
     * Buscar carpetas por nombre
     */
    public function search($query)
    {
        $sql = "SELECT * FROM {$this->table} WHERE name LIKE ? ORDER BY name ASC";
        return $this->query($sql, ['%' . $query . '%']);
    }

    /**
     * Verificar si una carpeta tiene hijos
     */
    public function hasChildren($folderId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE parent_id = ?";
        $result = $this->query($sql, [$folderId]);

        return $result[0]['count'] > 0;
    }
}
