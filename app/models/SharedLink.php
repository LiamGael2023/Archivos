<?php

require_once __DIR__ . '/../../core/Model.php';

class SharedLink extends Model
{
    protected $table = 'shared_links';

    /**
     * Crear un enlace compartido
     */
    public function createLink($entityType, $entityId, $expiresAt = null)
    {
        if (!in_array($entityType, ['file', 'folder'])) {
            throw new Exception("Tipo de entidad inválido. Debe ser 'file' o 'folder'");
        }

        // Generar token único
        $token = $this->generateUniqueToken();

        $data = [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'token' => $token,
            'is_active' => 1,
            'expires_at' => $expiresAt,
            'download_count' => 0
        ];

        $id = $this->create($data);

        if ($id) {
            return $this->find($id);
        }

        return false;
    }

    /**
     * Generar token único
     */
    private function generateUniqueToken($length = 32)
    {
        do {
            $token = bin2hex(random_bytes($length / 2));
            $existing = $this->whereFirst('token', '=', $token);
        } while ($existing);

        return $token;
    }

    /**
     * Obtener enlace por token
     */
    public function getByToken($token)
    {
        return $this->whereFirst('token', '=', $token);
    }

    /**
     * Verificar si un enlace es válido
     */
    public function isValidLink($token)
    {
        $link = $this->getByToken($token);

        if (!$link) {
            return false;
        }

        // Verificar si está activo
        if (!$link['is_active']) {
            return false;
        }

        // Verificar si ha expirado
        if ($link['expires_at'] !== null) {
            $expiresAt = new DateTime($link['expires_at']);
            $now = new DateTime();

            if ($now > $expiresAt) {
                // Desactivar el enlace automáticamente
                $this->update($link['id'], ['is_active' => 0]);
                return false;
            }
        }

        return true;
    }

    /**
     * Incrementar contador de descargas
     */
    public function incrementDownloadCount($token)
    {
        $link = $this->getByToken($token);

        if (!$link) {
            return false;
        }

        $sql = "UPDATE {$this->table}
                SET download_count = download_count + 1
                WHERE token = ?";

        return $this->execute($sql, [$token]);
    }

    /**
     * Obtener todos los enlaces de una entidad
     */
    public function getLinksByEntity($entityType, $entityId)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE entity_type = ? AND entity_id = ?
                ORDER BY created_at DESC";

        return $this->query($sql, [$entityType, $entityId]);
    }

    /**
     * Obtener enlaces activos de una entidad
     */
    public function getActiveLinksByEntity($entityType, $entityId)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE entity_type = ? AND entity_id = ? AND is_active = 1
                AND (expires_at IS NULL OR expires_at > GETDATE())
                ORDER BY created_at DESC";

        return $this->query($sql, [$entityType, $entityId]);
    }

    /**
     * Desactivar un enlace
     */
    public function deactivateLink($token)
    {
        $link = $this->getByToken($token);

        if (!$link) {
            return false;
        }

        return $this->update($link['id'], ['is_active' => 0]);
    }

    /**
     * Activar un enlace
     */
    public function activateLink($token)
    {
        $link = $this->getByToken($token);

        if (!$link) {
            return false;
        }

        return $this->update($link['id'], ['is_active' => 1]);
    }

    /**
     * Eliminar un enlace
     */
    public function deleteLink($token)
    {
        $link = $this->getByToken($token);

        if (!$link) {
            return false;
        }

        return $this->delete($link['id']);
    }

    /**
     * Actualizar fecha de expiración
     */
    public function updateExpiration($token, $expiresAt)
    {
        $link = $this->getByToken($token);

        if (!$link) {
            return false;
        }

        return $this->update($link['id'], ['expires_at' => $expiresAt]);
    }

    /**
     * Obtener información completa del enlace con la entidad
     */
    public function getLinkWithEntity($token)
    {
        $sql = "SELECT sl.*,
                CASE
                    WHEN sl.entity_type = 'file' THEN f.name
                    WHEN sl.entity_type = 'folder' THEN fo.name
                END as entity_name
                FROM {$this->table} sl
                LEFT JOIN files f ON sl.entity_type = 'file' AND sl.entity_id = f.id
                LEFT JOIN folders fo ON sl.entity_type = 'folder' AND sl.entity_id = fo.id
                WHERE sl.token = ?";

        $result = $this->query($sql, [$token]);
        return $result[0] ?? null;
    }

    /**
     * Limpiar enlaces expirados
     */
    public function cleanExpiredLinks()
    {
        $sql = "UPDATE {$this->table}
                SET is_active = 0
                WHERE expires_at IS NOT NULL
                AND expires_at < GETDATE()
                AND is_active = 1";

        return $this->execute($sql);
    }

    /**
     * Obtener estadísticas de enlaces
     */
    public function getStats($entityType = null)
    {
        $sql = "SELECT
                    COUNT(*) as total_links,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_links,
                    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_links,
                    SUM(download_count) as total_downloads,
                    AVG(CAST(download_count AS FLOAT)) as avg_downloads
                FROM {$this->table}";

        $params = [];

        if ($entityType !== null) {
            $sql .= " WHERE entity_type = ?";
            $params[] = $entityType;
        }

        $result = $this->query($sql, $params);
        return $result[0] ?? null;
    }

    /**
     * Generar URL completa del enlace
     */
    public function getPublicUrl($token, $baseUrl = null)
    {
        if ($baseUrl === null) {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseUrl = $protocol . '://' . $host;
        }

        return $baseUrl . '/shared/' . $token;
    }
}
