<?php

namespace App\Http\Controllers;

use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;

class GoogleDriveController extends Controller
{
    protected $googleDriveService;

    public function __construct(GoogleDriveService $googleDriveService)
    {
        $this->googleDriveService = $googleDriveService;
        // Temporalmente removido para configuración inicial
        // $this->middleware('permission:admin.manage')->only(['setup', 'handleCallback', 'testConnection']);
    }

    /**
     * Mostrar página de configuración de Google Drive
     */
    public function setup()
    {
        $accessToken = config('services.google.access_token');
        $isConfigured = !empty($accessToken);
        $authUrl = $isConfigured ? null : $this->googleDriveService->getAuthUrl();
        
        return view('admin.google-drive-setup', compact('isConfigured', 'authUrl'));
    }

    /**
     * Manejar el callback de autorización de Google
     */
    public function handleCallback(Request $request)
    {
        $code = $request->get('code');
        
        if (!$code) {
            return redirect()->route('admin.google-drive.setup')
                ->withErrors(['error' => 'No se recibió el código de autorización']);
        }

        try {
            $tokens = $this->googleDriveService->handleAuthCallback($code);
            
            // Actualizar el archivo .env.production con los tokens
            $this->updateEnvFile($tokens);
            
            Log::info('Tokens de Google Drive obtenidos y guardados', $tokens);
            
            return redirect()->route('admin.google-drive.setup')
                ->with('success', 'Google Drive configurado correctamente. Los tokens se han guardado en el archivo .env');
            
        } catch (\Exception $e) {
            Log::error('Error en callback de Google Drive: ' . $e->getMessage());
            
            return redirect()->route('admin.google-drive.setup')
                ->withErrors(['error' => 'Error al configurar Google Drive: ' . $e->getMessage()]);
        }
    }

    /**
     * Actualizar el archivo .env con los tokens de Google
     */
    private function updateEnvFile($tokens)
    {
        $envPath = base_path('.env');
        
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            
            // Actualizar GOOGLE_ACCESS_TOKEN
            if (isset($tokens['access_token'])) {
                $envContent = preg_replace(
                    '/^GOOGLE_ACCESS_TOKEN=.*$/m',
                    'GOOGLE_ACCESS_TOKEN=' . $tokens['access_token'],
                    $envContent
                );
            }
            
            // Actualizar GOOGLE_REFRESH_TOKEN
            if (isset($tokens['refresh_token'])) {
                $envContent = preg_replace(
                    '/^GOOGLE_REFRESH_TOKEN=.*$/m',
                    'GOOGLE_REFRESH_TOKEN=' . $tokens['refresh_token'],
                    $envContent
                );
            }
            
            file_put_contents($envPath, $envContent);
            
            Log::info('Tokens de Google Drive guardados en archivo .env', [
                'access_token' => substr($tokens['access_token'] ?? '', 0, 20) . '...',
                'has_refresh_token' => isset($tokens['refresh_token'])
            ]);
        } else {
            Log::error('Archivo .env no encontrado en: ' . $envPath);
        }
    }

    /**
     * Probar la conexión con Google Drive
     */
    public function testConnection()
    {
        try {
            // Intentar crear una carpeta de prueba
            $folder = $this->googleDriveService->createFolder('Laravel Test - ' . now()->format('Y-m-d H:i:s'));
            
            // Eliminar la carpeta de prueba
            $this->googleDriveService->deleteFile($folder['id']);
            
            return response()->json([
                'success' => true,
                'message' => 'Conexión con Google Drive exitosa'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error probando conexión con Google Drive: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear carpeta principal para la aplicación
     */
    public function createMainFolder(Request $request)
    {
        $validated = $request->validate([
            'folder_name' => 'required|string|max:255'
        ]);

        try {
            $folder = $this->googleDriveService->createFolder($validated['folder_name']);
            
            return response()->json([
                'success' => true,
                'message' => 'Carpeta creada correctamente',
                'folder' => $folder
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error creando carpeta principal: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear carpeta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Diagnóstico de configuración de Google Drive
     */
    public function diagnose()
    {
        $diagnostics = [];
        
        // Verificar configuración básica
        $diagnostics['config'] = [
            'client_id' => config('services.google.client_id') ? 'Configurado' : 'No configurado',
            'client_secret' => config('services.google.client_secret') ? 'Configurado' : 'No configurado',
            'redirect_uri' => config('services.google.redirect_uri'),
            'access_token' => config('services.google.access_token') ? 'Configurado' : 'No configurado',
        ];
        
        // Verificar si Google Client está disponible
        $diagnostics['google_client'] = class_exists('Google\Client') ? 'Disponible' : 'No disponible';
        
        // Intentar crear cliente de Google
        try {
            $client = new \Google\Client();
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $client->setRedirectUri(config('services.google.redirect_uri'));
            
            $diagnostics['client_creation'] = 'Exitoso';
            
            // Intentar obtener URL de autorización
            try {
                $authUrl = $client->createAuthUrl();
                $diagnostics['auth_url'] = 'Disponible';
                $diagnostics['auth_url_value'] = $authUrl;
            } catch (\Exception $e) {
                $diagnostics['auth_url'] = 'Error: ' . $e->getMessage();
            }
            
        } catch (\Exception $e) {
            $diagnostics['client_creation'] = 'Error: ' . $e->getMessage();
        }
        
        // Verificar GoogleDriveService
        try {
            $service = new GoogleDriveService();
            $diagnostics['service_creation'] = 'Exitoso';
            
            // Intentar obtener URL de auth desde el servicio
            try {
                $authUrl = $service->getAuthUrl();
                $diagnostics['service_auth_url'] = 'Disponible';
            } catch (\Exception $e) {
                $diagnostics['service_auth_url'] = 'Error: ' . $e->getMessage();
            }
            
        } catch (\Exception $e) {
            $diagnostics['service_creation'] = 'Error: ' . $e->getMessage();
        }
        
        return view('admin.google-drive-diagnose', compact('diagnostics'));
    }
}
