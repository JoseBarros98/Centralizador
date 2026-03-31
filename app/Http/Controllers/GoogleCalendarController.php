<?php

namespace App\Http\Controllers;

use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class GoogleCalendarController extends Controller
{
    public function __construct(private GoogleCalendarService $googleCalendarService)
    {
    }

    public function setup()
    {
        $accessToken = config('services.google_calendar.access_token');
        $isConfigured = !empty($accessToken);
        $authUrl = $this->googleCalendarService->getAuthUrl();
        $requestedScopes = GoogleCalendarService::getRequestedScopes();

        return view('admin.google-calendar-setup', compact('isConfigured', 'authUrl', 'requestedScopes'));
    }

    public function handleCallback(Request $request)
    {
        $code = $request->get('code');

        if (!$code) {
            return redirect()->route('admin.google-calendar.setup')
                ->withErrors(['error' => 'No se recibió el código de autorización de Google Calendar.']);
        }

        try {
            $tokens = $this->googleCalendarService->handleAuthCallback($code);
            $this->updateEnvFile($tokens);

            Log::info('Tokens de Google Calendar obtenidos y guardados.');

            return redirect()->route('admin.google-calendar.setup')
                ->with('success', 'Google Calendar y Google Meet se autorizaron correctamente. Los tokens se guardaron en el archivo .env');
        } catch (\Throwable $exception) {
            Log::error('Error en callback de Google Calendar: ' . $exception->getMessage());

            return redirect()->route('admin.google-calendar.setup')
                ->withErrors(['error' => 'Error al configurar Google Calendar: ' . $exception->getMessage()]);
        }
    }

    public function testConnection()
    {
        try {
            $this->googleCalendarService->testConnection();

            return response()->json([
                'success' => true,
                'message' => 'Conexión con Google Calendar exitosa.',
            ]);
        } catch (\Throwable $exception) {
            Log::error('Error probando conexión con Google Calendar: ' . $exception->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error de conexión: ' . $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * @param array<string, mixed> $tokens
     */
    private function updateEnvFile(array $tokens): void
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            Log::error('Archivo .env no encontrado en: ' . $envPath);
            return;
        }

        $envContent = file_get_contents($envPath);

        if (isset($tokens['access_token'])) {
            $envContent = preg_replace(
                '/^GOOGLE_CALENDAR_ACCESS_TOKEN=.*$/m',
                'GOOGLE_CALENDAR_ACCESS_TOKEN="' . addslashes((string) $tokens['access_token']) . '"',
                $envContent
            );
        }

        if (isset($tokens['refresh_token'])) {
            $envContent = preg_replace(
                '/^GOOGLE_CALENDAR_REFRESH_TOKEN=.*$/m',
                'GOOGLE_CALENDAR_REFRESH_TOKEN="' . addslashes((string) $tokens['refresh_token']) . '"',
                $envContent
            );
        }

        file_put_contents($envPath, $envContent);
    }
}
