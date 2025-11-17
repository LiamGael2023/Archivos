<?php

/**
 * Definición de rutas de la aplicación
 */

// ============================================
// Rutas de Carpetas (Folders)
// ============================================

// Vista principal (explorador de archivos)
$router->get('/', 'FolderController@index');
$router->get('folders', 'FolderController@index');

// API REST para carpetas
$router->post('api/folders', 'FolderController@create');
$router->put('api/folders/{id}', 'FolderController@update');
$router->delete('api/folders/{id}', 'FolderController@delete');

// Funcionalidades adicionales de carpetas
$router->get('api/folders/tree', 'FolderController@tree');
$router->get('api/folders/search', 'FolderController@search');

// MetaKeys de carpetas
$router->post('api/folders/{id}/metakeys', 'FolderController@addMetaKey');
$router->get('api/folders/{id}/metakeys', 'FolderController@getMetaKeys');

// Enlaces compartidos de carpetas
$router->post('api/folders/{id}/share', 'FolderController@createShareLink');
$router->get('api/folders/{id}/shares', 'FolderController@getShareLinks');

// ============================================
// Rutas de Archivos (Files)
// ============================================

// API REST para archivos
$router->post('api/files/upload', 'FileController@upload');
$router->put('api/files/{id}', 'FileController@update');
$router->post('api/files/{id}/move', 'FileController@move');
$router->delete('api/files/{id}', 'FileController@delete');

// Descarga de archivos
$router->get('api/files/{id}/download', 'FileController@download');

// Búsqueda de archivos
$router->get('api/files/search', 'FileController@search');
$router->get('api/files/search-by-metakeys', 'FileController@searchByMetaKeys');

// MetaKeys de archivos
$router->post('api/files/{id}/metakeys', 'FileController@addMetaKey');
$router->get('api/files/{id}/metakeys', 'FileController@getMetaKeys');

// Enlaces compartidos de archivos
$router->post('api/files/{id}/share', 'FileController@createShareLink');
$router->get('api/files/{id}/shares', 'FileController@getShareLinks');

// ============================================
// Rutas de Enlaces Compartidos
// ============================================

// Ver contenido compartido
$router->get('shared/{token}', 'SharedController@view');
$router->get('shared/{token}/download', 'SharedController@download');

// Gestión de enlaces
$router->post('api/shared/{token}/deactivate', 'SharedController@deactivate');
$router->delete('api/shared/{token}', 'SharedController@delete');
