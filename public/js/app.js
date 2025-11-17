/**
 * Sistema de Archivos - Frontend JavaScript
 */

class FileStorage {
    constructor() {
        this.currentFolderId = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Crear carpeta
        const createFolderBtn = document.getElementById('createFolderBtn');
        if (createFolderBtn) {
            createFolderBtn.addEventListener('click', () => this.showCreateFolderModal());
        }

        // Subir archivo
        const uploadFileBtn = document.getElementById('uploadFileBtn');
        if (uploadFileBtn) {
            uploadFileBtn.addEventListener('click', () => this.showUploadFileModal());
        }

        // Búsqueda
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => this.handleSearch(e.target.value));
        }

        // Ordenamiento
        const sortSelect = document.getElementById('sortBy');
        const directionSelect = document.getElementById('direction');

        if (sortSelect) {
            sortSelect.addEventListener('change', () => this.handleSort());
        }

        if (directionSelect) {
            directionSelect.addEventListener('change', () => this.handleSort());
        }
    }

    // ============================================
    // Carpetas
    // ============================================

    showCreateFolderModal() {
        const modal = document.getElementById('createFolderModal');
        if (modal) {
            modal.classList.add('active');
        }
    }

    hideCreateFolderModal() {
        const modal = document.getElementById('createFolderModal');
        if (modal) {
            modal.classList.remove('active');
            document.getElementById('folderNameInput').value = '';
        }
    }

    async createFolder() {
        const name = document.getElementById('folderNameInput').value;

        if (!name.trim()) {
            this.showAlert('Por favor ingresa un nombre para la carpeta', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('name', name);

        if (this.currentFolderId) {
            formData.append('parent_id', this.currentFolderId);
        }

        try {
            const response = await fetch('/api/folders', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('Carpeta creada exitosamente', 'success');
                this.hideCreateFolderModal();
                location.reload();
            } else {
                this.showAlert(data.message || 'Error al crear carpeta', 'error');
            }
        } catch (error) {
            this.showAlert('Error de conexión: ' + error.message, 'error');
        }
    }

    async deleteFolder(folderId, folderName) {
        if (!confirm(`¿Estás seguro de eliminar la carpeta "${folderName}"? Esta acción eliminará todo su contenido.`)) {
            return;
        }

        try {
            const response = await fetch(`/api/folders/${folderId}`, {
                method: 'DELETE'
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('Carpeta eliminada exitosamente', 'success');
                location.reload();
            } else {
                this.showAlert(data.message || 'Error al eliminar carpeta', 'error');
            }
        } catch (error) {
            this.showAlert('Error de conexión: ' + error.message, 'error');
        }
    }

    async renameFolder(folderId) {
        const newName = prompt('Ingresa el nuevo nombre de la carpeta:');

        if (!newName || !newName.trim()) {
            return;
        }

        const formData = new FormData();
        formData.append('name', newName);
        formData.append('_method', 'PUT');

        try {
            const response = await fetch(`/api/folders/${folderId}`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('Carpeta renombrada exitosamente', 'success');
                location.reload();
            } else {
                this.showAlert(data.message || 'Error al renombrar carpeta', 'error');
            }
        } catch (error) {
            this.showAlert('Error de conexión: ' + error.message, 'error');
        }
    }

    // ============================================
    // Archivos
    // ============================================

    showUploadFileModal() {
        const modal = document.getElementById('uploadFileModal');
        if (modal) {
            modal.classList.add('active');
        }
    }

    hideUploadFileModal() {
        const modal = document.getElementById('uploadFileModal');
        if (modal) {
            modal.classList.remove('active');
            document.getElementById('fileInput').value = '';
        }
    }

    async uploadFile() {
        const fileInput = document.getElementById('fileInput');
        const file = fileInput.files[0];

        if (!file) {
            this.showAlert('Por favor selecciona un archivo', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('file', file);

        if (this.currentFolderId) {
            formData.append('folder_id', this.currentFolderId);
        }

        try {
            const response = await fetch('/api/files/upload', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('Archivo subido exitosamente', 'success');
                this.hideUploadFileModal();
                location.reload();
            } else {
                this.showAlert(data.message || 'Error al subir archivo', 'error');
            }
        } catch (error) {
            this.showAlert('Error de conexión: ' + error.message, 'error');
        }
    }

    async deleteFile(fileId, fileName) {
        if (!confirm(`¿Estás seguro de eliminar el archivo "${fileName}"?`)) {
            return;
        }

        try {
            const response = await fetch(`/api/files/${fileId}`, {
                method: 'DELETE'
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('Archivo eliminado exitosamente', 'success');
                location.reload();
            } else {
                this.showAlert(data.message || 'Error al eliminar archivo', 'error');
            }
        } catch (error) {
            this.showAlert('Error de conexión: ' + error.message, 'error');
        }
    }

    async renameFile(fileId) {
        const newName = prompt('Ingresa el nuevo nombre del archivo:');

        if (!newName || !newName.trim()) {
            return;
        }

        const formData = new FormData();
        formData.append('name', newName);
        formData.append('_method', 'PUT');

        try {
            const response = await fetch(`/api/files/${fileId}`, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('Archivo renombrado exitosamente', 'success');
                location.reload();
            } else {
                this.showAlert(data.message || 'Error al renombrar archivo', 'error');
            }
        } catch (error) {
            this.showAlert('Error de conexión: ' + error.message, 'error');
        }
    }

    // ============================================
    // Enlaces compartidos
    // ============================================

    async shareFile(fileId) {
        try {
            const response = await fetch(`/api/files/${fileId}/share`, {
                method: 'POST'
            });

            const data = await response.json();

            if (data.success) {
                this.showShareLinkModal(data.public_url);
            } else {
                this.showAlert(data.message || 'Error al crear enlace', 'error');
            }
        } catch (error) {
            this.showAlert('Error de conexión: ' + error.message, 'error');
        }
    }

    async shareFolder(folderId) {
        try {
            const response = await fetch(`/api/folders/${folderId}/share`, {
                method: 'POST'
            });

            const data = await response.json();

            if (data.success) {
                this.showShareLinkModal(data.public_url);
            } else {
                this.showAlert(data.message || 'Error al crear enlace', 'error');
            }
        } catch (error) {
            this.showAlert('Error de conexión: ' + error.message, 'error');
        }
    }

    showShareLinkModal(url) {
        const modal = document.getElementById('shareLinkModal');
        const linkInput = document.getElementById('shareLinkInput');

        if (modal && linkInput) {
            linkInput.value = url;
            modal.classList.add('active');
        }
    }

    hideShareLinkModal() {
        const modal = document.getElementById('shareLinkModal');
        if (modal) {
            modal.classList.remove('active');
        }
    }

    copyShareLink() {
        const linkInput = document.getElementById('shareLinkInput');

        if (linkInput) {
            linkInput.select();
            document.execCommand('copy');
            this.showAlert('Enlace copiado al portapapeles', 'success');
        }
    }

    // ============================================
    // Búsqueda y ordenamiento
    // ============================================

    async handleSearch(query) {
        if (query.length < 2) {
            return;
        }

        // Implementar búsqueda en tiempo real
        console.log('Buscando:', query);
    }

    handleSort() {
        const sortBy = document.getElementById('sortBy').value;
        const direction = document.getElementById('direction').value;

        const url = new URL(window.location);
        url.searchParams.set('orderBy', sortBy);
        url.searchParams.set('direction', direction);

        window.location.href = url.toString();
    }

    // ============================================
    // Utilidades
    // ============================================

    showAlert(message, type = 'info') {
        const alertContainer = document.getElementById('alertContainer');

        if (!alertContainer) {
            // Crear contenedor si no existe
            const container = document.createElement('div');
            container.id = 'alertContainer';
            container.style.position = 'fixed';
            container.style.top = '20px';
            container.style.right = '20px';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }

        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        alert.style.marginBottom = '10px';
        alert.style.minWidth = '300px';

        document.getElementById('alertContainer').appendChild(alert);

        // Auto-remover después de 5 segundos
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';

        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
}

// Inicializar aplicación
document.addEventListener('DOMContentLoaded', () => {
    window.fileStorage = new FileStorage();
});
