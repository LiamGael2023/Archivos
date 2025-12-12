<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/SharedLink.php';
require_once __DIR__ . '/../models/File.php';
require_once __DIR__ . '/../models/Folder.php';

class SharedController extends Controller
{
    private $sharedLinkModel;
    private $fileModel;
    private $folderModel;

    public function __construct()
    {
        $this->sharedLinkModel = new SharedLink();
        $this->fileModel = new File();
        $this->folderModel = new Folder();
    }

    /**
     * Ver contenido compartido por token
     */
    public function view($token)
    {
        try {
            // Verificar que el enlace es válido
            if (!$this->sharedLinkModel->isValidLink($token)) {
                $this->view('shared.invalid', [
                    'message' => 'Este enlace no es válido o ha expirado'
                ]);
                return;
            }

            // Obtener información del enlace
            $link = $this->sharedLinkModel->getLinkWithEntity($token);

            if (!$link) {
                $this->view('shared.invalid', [
                    'message' => 'Enlace no encontrado'
                ]);
                return;
            }

            // Incrementar contador de descargas
            $this->sharedLinkModel->incrementDownloadCount($token);

            // Mostrar según tipo
            if ($link['entity_type'] === 'file') {
                $file = $this->fileModel->find($link['entity_id']);

                if (!$file) {
                    $this->view('shared.invalid', [
                        'message' => 'Archivo no encontrado'
                    ]);
                    return;
                }

                $this->view('shared.file', [
                    'file' => $file,
                    'link' => $link,
                    'token' => $token
                ]);

            } else if ($link['entity_type'] === 'folder') {
                $folder = $this->folderModel->find($link['entity_id']);

                if (!$folder) {
                    $this->view('shared.invalid', [
                        'message' => 'Carpeta no encontrada'
                    ]);
                    return;
                }

                // Obtener archivos de la carpeta
                $files = $this->fileModel->getByFolder($link['entity_id']);

                // Obtener subcarpetas
                $subfolders = $this->folderModel->getChildren($link['entity_id']);

                // Crear breadcrumb inicial
                $breadcrumb = [$folder];

                $this->view('shared.folder', [
                    'folder' => $folder,
                    'files' => $files,
                    'subfolders' => $subfolders,
                    'link' => $link,
                    'token' => $token,
                    'breadcrumb' => $breadcrumb,
                    'isSubfolder' => false
                ]);
            }

        } catch (Exception $e) {
            $this->view('shared.error', [
                'message' => 'Error al cargar contenido compartido: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Ver subcarpeta dentro de una carpeta compartida
     */
    public function viewFolder($token, $folderId)
    {
        try {
            // Verificar que el enlace es válido
            if (!$this->sharedLinkModel->isValidLink($token)) {
                $this->view('shared.invalid', [
                    'message' => 'Este enlace no es válido o ha expirado'
                ]);
                return;
            }

            // Obtener información del enlace
            $link = $this->sharedLinkModel->getLinkWithEntity($token);

            if (!$link || $link['entity_type'] !== 'folder') {
                $this->view('shared.invalid', [
                    'message' => 'Este enlace no corresponde a una carpeta'
                ]);
                return;
            }

            // Verificar que la subcarpeta solicitada es descendiente de la carpeta compartida
            if (!$this->folderModel->isDescendantOf($folderId, $link['entity_id'])) {
                $this->view('shared.invalid', [
                    'message' => 'No tienes acceso a esta carpeta'
                ]);
                return;
            }

            // Obtener la subcarpeta solicitada
            $folder = $this->folderModel->find($folderId);

            if (!$folder) {
                $this->view('shared.invalid', [
                    'message' => 'Carpeta no encontrada'
                ]);
                return;
            }

            // Obtener archivos de la carpeta
            $files = $this->fileModel->getByFolder($folderId);

            // Obtener subcarpetas
            $subfolders = $this->folderModel->getChildren($folderId);

            // Obtener breadcrumb desde la carpeta compartida original
            $breadcrumb = $this->folderModel->getBreadcrumb($folderId);

            // Filtrar breadcrumb para mostrar solo desde la carpeta compartida
            $filteredBreadcrumb = [];
            $startAdding = false;
            foreach ($breadcrumb as $crumb) {
                if ($crumb['id'] == $link['entity_id']) {
                    $startAdding = true;
                }
                if ($startAdding) {
                    $filteredBreadcrumb[] = $crumb;
                }
            }

            // Incrementar contador de descargas
            $this->sharedLinkModel->incrementDownloadCount($token);

            $this->view('shared.folder', [
                'folder' => $folder,
                'files' => $files,
                'subfolders' => $subfolders,
                'link' => $link,
                'token' => $token,
                'breadcrumb' => $filteredBreadcrumb,
                'isSubfolder' => true
            ]);

        } catch (Exception $e) {
            $this->view('shared.error', [
                'message' => 'Error al cargar contenido compartido: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Descargar archivo compartido
     */
    public function download($token)
    {
        try {
            // Verificar que el enlace es válido
            if (!$this->sharedLinkModel->isValidLink($token)) {
                http_response_code(403);
                echo 'Enlace no válido o expirado';
                return;
            }

            // Obtener información del enlace
            $link = $this->sharedLinkModel->getByToken($token);

            if (!$link || $link['entity_type'] !== 'file') {
                http_response_code(404);
                echo 'Archivo no encontrado';
                return;
            }

            // Obtener archivo
            $file = $this->fileModel->find($link['entity_id']);

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

            // Incrementar contador
            $this->sharedLinkModel->incrementDownloadCount($token);

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
     * Desactivar enlace compartido (API)
     */
    public function deactivate($token)
    {
        try {
            $result = $this->sharedLinkModel->deactivateLink($token);

            if ($result) {
                return $this->json([
                    'success' => true,
                    'message' => 'Enlace desactivado exitosamente'
                ]);
            }

            return $this->json([
                'success' => false,
                'message' => 'Enlace no encontrado'
            ], 404);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al desactivar enlace: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar enlace compartido (API)
     */
    public function delete($token)
    {
        try {
            $result = $this->sharedLinkModel->deleteLink($token);

            if ($result) {
                return $this->json([
                    'success' => true,
                    'message' => 'Enlace eliminado exitosamente'
                ]);
            }

            return $this->json([
                'success' => false,
                'message' => 'Enlace no encontrado'
            ], 404);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al eliminar enlace: ' . $e->getMessage()
            ], 500);
        }
    }
}
