<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/File.php';
require_once __DIR__ . '/../models/Folder.php';
require_once __DIR__ . '/../models/MetaKey.php';
require_once __DIR__ . '/../models/SharedLink.php';

class FileController extends Controller
{
    private $fileModel;
    private $folderModel;
    private $metaKeyModel;
    private $sharedLinkModel;
    private $uploadDir;

    public function __construct()
    {
        $this->fileModel = new File();
        $this->folderModel = new Folder();
        $this->metaKeyModel = new MetaKey();
        $this->sharedLinkModel = new SharedLink();
        $this->uploadDir = __DIR__ . '/../../public/uploads/';
    }

    /**
     * Subir archivo (API)
     */
    public function upload()
    {
        if (!isset($_FILES['file'])) {
            return $this->json([
                'success' => false,
                'message' => 'No se ha enviado ningún archivo'
            ], 400);
        }

        $file = $_FILES['file'];
        $folderId = $this->input('folder_id');

        // Validar errores de upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $this->json([
                'success' => false,
                'message' => 'Error al subir archivo: ' . $this->getUploadErrorMessage($file['error'])
            ], 400);
        }

        try {
            // Información del archivo
            $originalName = basename($file['name']);
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $size = $file['size'];
            $mimeType = mime_content_type($file['tmp_name']);

            // Generar nombre único
            $uniqueName = uniqid() . '_' . time() . '.' . $extension;

            // Crear estructura de directorios por fecha
            $dateDir = date('Y/m/d');
            $uploadPath = $this->uploadDir . $dateDir . '/';

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Mover archivo
            $fullPath = $uploadPath . $uniqueName;

            if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
                throw new Exception('Error al mover el archivo al directorio de destino');
            }

            // Guardar en base de datos
            $relativePath = '/uploads/' . $dateDir . '/' . $uniqueName;

            $fileData = [
                'name' => pathinfo($originalName, PATHINFO_FILENAME),
                'original_name' => $originalName,
                'extension' => $extension,
                'size' => $size,
                'mime_type' => $mimeType,
                'path' => $relativePath,
                'folder_id' => $folderId ?: null
            ];

            $fileId = $this->fileModel->createFile($fileData);

            return $this->json([
                'success' => true,
                'message' => 'Archivo subido exitosamente',
                'file_id' => $fileId,
                'file' => array_merge(['id' => $fileId], $fileData)
            ]);

        } catch (Exception $e) {
            // Eliminar archivo si existe
            if (isset($fullPath) && file_exists($fullPath)) {
                unlink($fullPath);
            }

            return $this->json([
                'success' => false,
                'message' => 'Error al subir archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar nombre de archivo (API)
     */
    public function update($id)
    {
        $name = $this->input('name');

        $errors = $this->validate($_POST, [
            'name' => 'required'
        ]);

        if (!empty($errors)) {
            return $this->json(['success' => false, 'errors' => $errors], 400);
        }

        try {
            $result = $this->fileModel->updateFileName($id, $name);

            if ($result) {
                return $this->json([
                    'success' => true,
                    'message' => 'Archivo actualizado exitosamente'
                ]);
            }

            return $this->json([
                'success' => false,
                'message' => 'Archivo no encontrado'
            ], 404);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al actualizar archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mover archivo a otra carpeta (API)
     */
    public function move($id)
    {
        $folderId = $this->input('folder_id');

        try {
            // Verificar que la carpeta existe
            if ($folderId !== null) {
                $folder = $this->folderModel->find($folderId);

                if (!$folder) {
                    return $this->json([
                        'success' => false,
                        'message' => 'Carpeta no encontrada'
                    ], 404);
                }
            }

            $result = $this->fileModel->moveToFolder($id, $folderId);

            if ($result) {
                return $this->json([
                    'success' => true,
                    'message' => 'Archivo movido exitosamente'
                ]);
            }

            return $this->json([
                'success' => false,
                'message' => 'Archivo no encontrado'
            ], 404);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al mover archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar archivo (API)
     */
    public function delete($id)
    {
        try {
            $result = $this->fileModel->deleteFile($id);

            if ($result) {
                return $this->json([
                    'success' => true,
                    'message' => 'Archivo eliminado exitosamente'
                ]);
            }

            return $this->json([
                'success' => false,
                'message' => 'Archivo no encontrado'
            ], 404);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al eliminar archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar archivo
     */
    public function download($id)
    {
        try {
            $file = $this->fileModel->find($id);

            if (!$file) {
                http_response_code(404);
                echo 'Archivo no encontrado';
                return;
            }

            $fullPath = __DIR__ . '/../../public' . $file['path'];

            if (!file_exists($fullPath)) {
                http_response_code(404);
                echo 'Archivo no encontrado en el servidor';
                return;
            }

            // Headers para descarga
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $file['mime_type']);
            header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
            header('Content-Length: ' . $file['size']);
            header('Cache-Control: must-revalidate');
            header('Pragma: public');

            // Limpiar buffer
            ob_clean();
            flush();

            // Leer archivo
            readfile($fullPath);
            exit;

        } catch (Exception $e) {
            http_response_code(500);
            echo 'Error al descargar archivo: ' . $e->getMessage();
        }
    }

    /**
     * Buscar archivos (API)
     */
    public function search()
    {
        $query = $this->query('q');
        $searchType = $this->query('type', 'name');

        if (empty($query)) {
            return $this->json([
                'success' => false,
                'message' => 'Parámetro de búsqueda requerido'
            ], 400);
        }

        try {
            $results = $this->fileModel->search($query, $searchType);

            return $this->json([
                'success' => true,
                'results' => $results
            ]);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error en búsqueda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar por metakeys (API)
     */
    public function searchByMetaKeys()
    {
        $keyName = $this->query('key_name');
        $keyValue = $this->query('key_value');

        if (empty($keyName)) {
            return $this->json([
                'success' => false,
                'message' => 'Parámetro key_name requerido'
            ], 400);
        }

        try {
            $results = $this->metaKeyModel->searchByMetaKey($keyName, $keyValue, 'file');

            // Obtener información completa de archivos
            $files = [];
            foreach ($results as $result) {
                $file = $this->fileModel->find($result['entity_id']);
                if ($file) {
                    $files[] = $file;
                }
            }

            return $this->json([
                'success' => true,
                'results' => $files
            ]);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error en búsqueda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agregar metakey a archivo (API)
     */
    public function addMetaKey($id)
    {
        $keyName = $this->input('key_name');
        $keyValue = $this->input('key_value');

        $errors = $this->validate($_POST, [
            'key_name' => 'required',
            'key_value' => 'required'
        ]);

        if (!empty($errors)) {
            return $this->json(['success' => false, 'errors' => $errors], 400);
        }

        try {
            $metaKeyId = $this->metaKeyModel->addMetaKey('file', $id, $keyName, $keyValue);

            return $this->json([
                'success' => true,
                'message' => 'Metakey agregada exitosamente',
                'metakey_id' => $metaKeyId
            ]);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al agregar metakey: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener metakeys de archivo (API)
     */
    public function getMetaKeys($id)
    {
        try {
            $metaKeys = $this->metaKeyModel->getMetaKeys('file', $id);

            return $this->json([
                'success' => true,
                'metakeys' => $metaKeys
            ]);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener metakeys: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear enlace compartido para archivo (API)
     */
    public function createShareLink($id)
    {
        $expiresAt = $this->input('expires_at');

        try {
            $link = $this->sharedLinkModel->createLink('file', $id, $expiresAt);

            if ($link) {
                $publicUrl = $this->sharedLinkModel->getPublicUrl($link['token']);

                return $this->json([
                    'success' => true,
                    'message' => 'Enlace creado exitosamente',
                    'link' => $link,
                    'public_url' => $publicUrl
                ]);
            }

            return $this->json([
                'success' => false,
                'message' => 'Error al crear enlace'
            ], 500);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al crear enlace: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener enlaces compartidos de archivo (API)
     */
    public function getShareLinks($id)
    {
        try {
            $links = $this->sharedLinkModel->getLinksByEntity('file', $id);

            return $this->json([
                'success' => true,
                'links' => $links
            ]);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener enlaces: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener mensaje de error de upload
     */
    private function getUploadErrorMessage($errorCode)
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por el servidor',
            UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo permitido',
            UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
            UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta el directorio temporal',
            UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en disco',
            UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida del archivo'
        ];

        return $errors[$errorCode] ?? 'Error desconocido';
    }
}
