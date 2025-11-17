<?php

require_once __DIR__ . '/../../core/Model.php';

class MetaKey extends Model
{
    protected $table = 'metakeys';

    /**
     * Agregar una metakey a un archivo o carpeta
     */
    public function addMetaKey($entityType, $entityId, $keyName, $keyValue)
    {
        if (!in_array($entityType, ['file', 'folder'])) {
            throw new Exception("Tipo de entidad inválido. Debe ser 'file' o 'folder'");
        }

        $data = [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'key_name' => $keyName,
            'key_value' => $keyValue
        ];

        return $this->create($data);
    }

    /**
     * Obtener todas las metakeys de una entidad
     */
    public function getMetaKeys($entityType, $entityId)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE entity_type = ? AND entity_id = ?
                ORDER BY key_name ASC";

        return $this->query($sql, [$entityType, $entityId]);
    }

    /**
     * Obtener una metakey específica
     */
    public function getMetaKey($entityType, $entityId, $keyName)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE entity_type = ? AND entity_id = ? AND key_name = ?";

        $result = $this->query($sql, [$entityType, $entityId, $keyName]);
        return $result[0] ?? null;
    }

    /**
     * Actualizar o crear una metakey
     */
    public function setMetaKey($entityType, $entityId, $keyName, $keyValue)
    {
        $existing = $this->getMetaKey($entityType, $entityId, $keyName);

        if ($existing) {
            $sql = "UPDATE {$this->table}
                    SET key_value = ?
                    WHERE entity_type = ? AND entity_id = ? AND key_name = ?";

            return $this->execute($sql, [$keyValue, $entityType, $entityId, $keyName]);
        }

        return $this->addMetaKey($entityType, $entityId, $keyName, $keyValue);
    }

    /**
     * Eliminar una metakey específica
     */
    public function deleteMetaKey($entityType, $entityId, $keyName)
    {
        $sql = "DELETE FROM {$this->table}
                WHERE entity_type = ? AND entity_id = ? AND key_name = ?";

        return $this->execute($sql, [$entityType, $entityId, $keyName]);
    }

    /**
     * Eliminar todas las metakeys de una entidad
     */
    public function deleteAllMetaKeys($entityType, $entityId)
    {
        $sql = "DELETE FROM {$this->table}
                WHERE entity_type = ? AND entity_id = ?";

        return $this->execute($sql, [$entityType, $entityId]);
    }

    /**
     * Buscar archivos/carpetas por metakey
     */
    public function searchByMetaKey($keyName, $keyValue = null, $entityType = null)
    {
        $params = [$keyName];
        $sql = "SELECT DISTINCT entity_type, entity_id FROM {$this->table} WHERE key_name = ?";

        if ($keyValue !== null) {
            $sql .= " AND key_value LIKE ?";
            $params[] = '%' . $keyValue . '%';
        }

        if ($entityType !== null) {
            $sql .= " AND entity_type = ?";
            $params[] = $entityType;
        }

        return $this->query($sql, $params);
    }

    /**
     * Buscar archivos por múltiples metakeys (búsqueda avanzada)
     */
    public function searchByMultipleMetaKeys($criteria, $entityType = null)
    {
        // $criteria es un array de ['key_name' => 'value']
        if (empty($criteria)) {
            return [];
        }

        $conditions = [];
        $params = [];

        foreach ($criteria as $keyName => $keyValue) {
            $conditions[] = "(key_name = ? AND key_value LIKE ?)";
            $params[] = $keyName;
            $params[] = '%' . $keyValue . '%';
        }

        $sql = "SELECT entity_type, entity_id, COUNT(*) as matches
                FROM {$this->table}
                WHERE (" . implode(' OR ', $conditions) . ")";

        if ($entityType !== null) {
            $sql .= " AND entity_type = ?";
            $params[] = $entityType;
        }

        $sql .= " GROUP BY entity_type, entity_id
                  HAVING COUNT(*) >= ?
                  ORDER BY matches DESC";

        $params[] = count($criteria);

        return $this->query($sql, $params);
    }

    /**
     * Obtener todas las metakeys únicas (nombres)
     */
    public function getAllUniqueKeys($entityType = null)
    {
        $sql = "SELECT DISTINCT key_name FROM {$this->table}";
        $params = [];

        if ($entityType !== null) {
            $sql .= " WHERE entity_type = ?";
            $params[] = $entityType;
        }

        $sql .= " ORDER BY key_name ASC";

        return $this->query($sql, $params);
    }

    /**
     * Obtener todos los valores de una metakey específica
     */
    public function getValuesForKey($keyName, $entityType = null)
    {
        $sql = "SELECT DISTINCT key_value FROM {$this->table} WHERE key_name = ?";
        $params = [$keyName];

        if ($entityType !== null) {
            $sql .= " AND entity_type = ?";
            $params[] = $entityType;
        }

        $sql .= " ORDER BY key_value ASC";

        return $this->query($sql, $params);
    }

    /**
     * Obtener estadísticas de metakeys
     */
    public function getStats($entityType = null)
    {
        $sql = "SELECT
                    COUNT(*) as total_metakeys,
                    COUNT(DISTINCT key_name) as unique_keys,
                    COUNT(DISTINCT entity_id) as entities_with_metakeys
                FROM {$this->table}";

        $params = [];

        if ($entityType !== null) {
            $sql .= " WHERE entity_type = ?";
            $params[] = $entityType;
        }

        $result = $this->query($sql, $params);
        return $result[0] ?? null;
    }
}
