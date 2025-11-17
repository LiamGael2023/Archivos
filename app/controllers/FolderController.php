<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/Folder.php';
require_once __DIR__ . '/../models/File.php';
require_once __DIR__ . '/../models/MetaKey.php';
require_once __DIR__ . '/../models/SharedLink.php';

class FolderController extends Controller
{
    private $folderModel;
    private $fileModel;
    private $metaKeyModel;
    private $sharedLinkModel;

    public function __construct()
    {
        $this->folderModel = new Folder();
        $this->fileModel = new File();
        $this->metaKeyModel = new MetaKey();
        $this->sharedLinkModel = new SharedLink();
    }

    /**
     * Mostrar vista principal de explorador de archivos
     */
    public function index()
    {
        $folderId = $this->query('folder');
        $orderBy = $this->query('orderBy', 'name');
        $direction = $this->query('direction', 'ASC');

        // Obtener carpetas del nivel actual
        if ($folderId) {
            $currentFolder = $this->folderModel->find($folderId);
            $folders = $this->folderModel->getChildren($folderId);
            $breadcrumb = $this->folderModel->getBreadcrumb($folderId);
        } else {
            $currentFolder = null;
            $folders = $this->folderModel->getChildren(null);
            $breadcrumb = [];
        }

        // Obtener archivos del nivel actual
        $files = $this->fileModel->getByFolder($folderId, $orderBy, $direction);

        $this->view('folders.index', [
            'currentFolder' => $currentFolder,
            'folders' => $folders,
            'files' => $files,
            'breadcrumb' => $breadcrumb,
            'orderBy' => $orderBy,
            'direction' => $direction
        ]);
    }

    /**
     * Crear una nueva carpeta (API)
     */
    public function create()
    {
        $name = $this->input('name');
        $parentId = $this->input('parent_id');

        $errors = $this->validate($_POST, [
            'name' => 'required'
        ]);

        if (!empty($errors)) {
            return $this->json(['success' => false, 'errors' => $errors], 400);
        }

        try {
            $folderId = $this->folderModel->createFolder($name, $parentId);

            return $this->json([
                'success' => true,
                'message' => 'Carpeta creada exitosamente',
                'folder_id' => $folderId
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al crear carpeta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar nombre de carpeta (API)
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
            $result = $this->folderModel->updateFolder($id, $name);

            if ($result) {
                return $this->json([
                    'success' => true,
                    'message' => 'Carpeta actualizada exitosamente'
                ]);
            }

            return $this->json([
                'success' => false,
                'message' => 'Carpeta no encontrada'
            ], 404);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al actualizar carpeta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar carpeta (API)
     */
    public function delete($id)
    {
        try {
            $result = $this->folderModel->deleteFolder($id);

            if ($result) {
                return $this->json([
                    'success' => true,
                    'message' => 'Carpeta eliminada exitosamente'
                ]);
            }

            return $this->json([
                'success' => false,
                'message' => 'Carpeta no encontrada'
            ], 404);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al eliminar carpeta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener árbol completo de carpetas (API)
     */
    public function tree()
    {
        try {
            $tree = $this->folderModel->getTree();

            return $this->json([
                'success' => true,
                'tree' => $tree
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error al obtener árbol: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar carpetas (API)
     */
    public function search()
    {
        $query = $this->query('q');

        if (empty($query)) {
            return $this->json([
                'success' => false,
                'message' => 'Parámetro de búsqueda requerido'
            ], 400);
        }

        try {
            $results = $this->folderModel->search($query);

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
     * Agregar metakey a carpeta (API)
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
            $metaKeyId = $this->metaKeyModel->addMetaKey('folder', $id, $keyName, $keyValue);

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
     * Obtener metakeys de carpeta (API)
     */
    public function getMetaKeys($id)
    {
        try {
            $metaKeys = $this->metaKeyModel->getMetaKeys('folder', $id);

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
     * Crear enlace compartido para carpeta (API)
     */
    public function createShareLink($id)
    {
        $expiresAt = $this->input('expires_at');

        try {
            $link = $this->sharedLinkModel->createLink('folder', $id, $expiresAt);

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
     * Obtener enlaces compartidos de carpeta (API)
     */
    public function getShareLinks($id)
    {
        try {
            $links = $this->sharedLinkModel->getLinksByEntity('folder', $id);

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
}
