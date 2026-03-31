<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Http\MediaFileUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    private $client;
    private $service;
    
    public function __construct()
    {
        $this->client = new Client();
        $this->client->setApplicationName('Laravel Google Drive');
        $this->client->setScopes([Drive::DRIVE_FILE]);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');
        
        // Configurar las credenciales desde las variables de entorno
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect_uri'));
        
        // Configurar el token de acceso si existe
        $accessToken = config('services.google.access_token');
        if ($accessToken) {
            $this->client->setAccessToken($accessToken);
            
            // Renovar el token si es necesario
            if ($this->client->isAccessTokenExpired()) {
                $this->refreshAccessToken();
            }
        }
        
        $this->service = new Drive($this->client);
    }
    
    /**
     * Subir un archivo a Google Drive
     */
    public function uploadFile($filePath, $fileName, $mimeType, $description = null, $folderId = null)
    {
        try {
            $fileMetadata = new DriveFile([
                'name' => $fileName,
                'description' => $description,
                'parents' => $folderId ? [$folderId] : null
            ]);
            
            $content = file_get_contents($filePath);
            
            $file = $this->service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id,name,webViewLink,webContentLink,size,createdTime'
            ]);
            
            Log::info('Archivo subido a Google Drive', [
                'file_id' => $file->getId(), 
                'name' => $fileName,
                'size' => $file->getSize(),
                'size_type' => gettype($file->getSize())
            ]);
            
            return [
                'id' => $file->getId(),
                'name' => $file->getName(),
                'webViewLink' => $file->getWebViewLink(),
                'webContentLink' => $file->getWebContentLink(),
                'size' => $file->getSize(),
                'createdTime' => $file->getCreatedTime()
            ];
            
        } catch (\Exception $e) {
            Log::error('Error subiendo archivo a Google Drive: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Descargar un archivo de Google Drive
     */
    public function downloadFile($fileId)
    {
        try {
            Log::info('Descargando archivo de Google Drive', ['file_id' => $fileId]);
            
            $response = $this->service->files->get($fileId, ['alt' => 'media']);
            
            Log::info('Respuesta recibida', [
                'response_type' => get_class($response),
                'response_size' => is_string($response) ? strlen($response) : 'No string'
            ]);
            
            // El API de Google Drive devuelve el contenido del archivo directamente como string
            if (is_string($response)) {
                Log::info('Respuesta es string directa');
                return $response;
            }
            
            // Si es un objeto Response de Google API Client, el contenido debería estar en el cuerpo
            if (is_object($response)) {
                Log::info('Procesando objeto response', ['methods' => get_class_methods($response)]);
                
                // Para Google API Client, a veces el contenido está directamente en el objeto
                if (method_exists($response, '__toString')) {
                    Log::info('Usando método __toString');
                    $content = (string) $response;
                    return $content;
                }
                
                // Si tiene un método getBody
                if (method_exists($response, 'getBody')) {
                    Log::info('Usando getBody()->getContents()');
                    /** @var mixed $body */
                    $body = call_user_func([$response, 'getBody']);
                    if (is_object($body) && method_exists($body, 'getContents')) {
                        return $body->getContents();
                    } elseif (is_object($body) && method_exists($body, '__toString')) {
                        return (string) $body;
                    } elseif (is_string($body)) {
                        return $body;
                    }
                }
                
                // Si tiene propiedad body
                if (isset($response->body)) {
                    Log::info('Usando property body');
                    return $response->body;
                }
            }
            
            // Si llegamos aquí, algo salió mal
            Log::error('No se pudo extraer el contenido del archivo', [
                'response_type' => is_object($response) ? get_class($response) : gettype($response),
                'response_debug' => print_r($response, true)
            ]);
            
            throw new \Exception('No se pudo extraer el contenido del archivo desde Google Drive');
            
        } catch (\Exception $e) {
            Log::error('Error descargando archivo de Google Drive', [
                'file_id' => $fileId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    /**
     * Eliminar un archivo de Google Drive
     */
    public function deleteFile($fileId)
    {
        try {
            $this->service->files->delete($fileId);
            Log::info('Archivo eliminado de Google Drive', ['file_id' => $fileId]);
            return true;
        } catch (\Exception $e) {
            Log::error('Error eliminando archivo de Google Drive: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtener información de un archivo
     */
    public function getFileInfo($fileId)
    {
        try {
            $file = $this->service->files->get($fileId, [
                'fields' => 'id,name,mimeType,size,createdTime,modifiedTime,webViewLink,webContentLink'
            ]);
            
            return [
                'id' => $file->getId(),
                'name' => $file->getName(),
                'mimeType' => $file->getMimeType(),
                'size' => $file->getSize(),
                'createdTime' => $file->getCreatedTime(),
                'modifiedTime' => $file->getModifiedTime(),
                'webViewLink' => $file->getWebViewLink(),
                'webContentLink' => $file->getWebContentLink()
            ];
        } catch (\Exception $e) {
            Log::error('Error obteniendo información del archivo: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Crear una carpeta en Google Drive
     */
    public function createFolder($name, $parentId = null)
    {
        try {
            $fileMetadata = new DriveFile([
                'name' => $name,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => $parentId ? [$parentId] : null
            ]);
            
            $folder = $this->service->files->create($fileMetadata, [
                'fields' => 'id,name,webViewLink'
            ]);
            
            Log::info('Carpeta creada en Google Drive', ['folder_id' => $folder->getId(), 'name' => $name]);
            
            return [
                'id' => $folder->getId(),
                'name' => $folder->getName(),
                'webViewLink' => $folder->getWebViewLink()
            ];
        } catch (\Exception $e) {
            Log::error('Error creando carpeta en Google Drive: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Renovar el token de acceso
     */
    private function refreshAccessToken()
    {
        $refreshToken = config('services.google.refresh_token');
        if ($refreshToken) {
            $this->client->refreshToken($refreshToken);
            $newAccessToken = $this->client->getAccessToken();
            
            // Aquí podrías guardar el nuevo token en la base de datos o en un archivo
            Log::info('Token de Google Drive renovado');
        }
    }
    
    /**
     * Obtener la URL de autorización para la primera configuración
     */
    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }
    
    /**
     * Procesar el código de autorización y obtener tokens
     */
    public function handleAuthCallback($code)
    {
        $this->client->fetchAccessTokenWithAuthCode($code);
        return $this->client->getAccessToken();
    }
    
    /**
     * Crear estructura jerárquica de carpetas
     * Estructura: Categoría Principal -> Secundaria -> Terciaria -> Cuaternaria (opcional)
     * Soporta 2, 3 o 4 niveles
     */
    public function createHierarchicalFolder($mainCategory, $secondaryFolder, $tertiaryFolder = null, $quaternaryFolder = null)
    {
        try {
            $baseFolderId = config('services.google.drive_folder_id');
            
            // Verificar si el folder base existe, si no, usar null (raíz)
            if ($baseFolderId) {
                try {
                    $this->service->files->get($baseFolderId);
                } catch (\Exception $e) {
                    Log::warning('Folder ID base no accesible, usando raíz de Drive', [
                        'folder_id' => $baseFolderId,
                        'error' => $e->getMessage()
                    ]);
                    $baseFolderId = null;
                }
            }
            
            // Paso 1: Crear o encontrar la carpeta principal (ej: "Solicitudes de Arte")
            $mainFolderId = $this->findOrCreateFolder($mainCategory, $baseFolderId);
            
            // Paso 2: Crear o encontrar la carpeta secundaria dentro de la principal
            $secondaryFolderId = $this->findOrCreateFolder($secondaryFolder, $mainFolderId);
            
            // Paso 3: Si hay carpeta terciaria, crearla dentro de la secundaria
            $tertiaryFolderId = $secondaryFolderId;
            if ($tertiaryFolder) {
                $tertiaryFolderId = $this->findOrCreateFolder($tertiaryFolder, $secondaryFolderId);
            }
            
            // Paso 4: Si hay carpeta cuaternaria, crearla dentro de la terciaria
            $finalFolderId = $tertiaryFolderId;
            if ($quaternaryFolder) {
                $finalFolderId = $this->findOrCreateFolder($quaternaryFolder, $tertiaryFolderId);
            }
            
            Log::info('Estructura jerárquica creada', [
                'main_category' => $mainCategory,
                'secondary_folder' => $secondaryFolder,
                'tertiary_folder' => $tertiaryFolder,
                'quaternary_folder' => $quaternaryFolder,
                'main_folder_id' => $mainFolderId,
                'secondary_folder_id' => $secondaryFolderId,
                'tertiary_folder_id' => $tertiaryFolderId,
                'final_folder_id' => $finalFolderId,
                'base_folder_id' => $baseFolderId
            ]);
            
            return $finalFolderId;
            
        } catch (\Exception $e) {
            Log::error('Error creando estructura jerárquica: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Buscar una carpeta por nombre, si no existe la crea
     */
    private function findOrCreateFolder($folderName, $parentId = null)
    {
        try {
            // Buscar si la carpeta ya existe
            $query = "mimeType='application/vnd.google-apps.folder' and name='" . addslashes($folderName) . "' and trashed=false";
            if ($parentId) {
                $query .= " and parents in '" . $parentId . "'";
            }
            
            $response = $this->service->files->listFiles([
                'q' => $query,
                'fields' => 'files(id,name)'
            ]);
            
            $files = $response->getFiles();
            
            if (count($files) > 0) {
                // La carpeta ya existe, devolver su ID
                $existingFolder = $files[0];
                Log::info('Carpeta existente encontrada', [
                    'name' => $folderName, 
                    'id' => $existingFolder->getId()
                ]);
                return $existingFolder->getId();
            } else {
                // La carpeta no existe, crearla
                $folderResult = $this->createFolder($folderName, $parentId);
                return $folderResult['id'];
            }
            
        } catch (\Exception $e) {
            Log::error('Error en findOrCreateFolder: ' . $e->getMessage());
            // Si falla, crear la carpeta directamente
            $folderResult = $this->createFolder($folderName, $parentId);
            return $folderResult['id'];
        }
    }
}
