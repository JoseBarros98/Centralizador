<?php

namespace App\Services;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Google\Client;
use Illuminate\Support\Facades\Log;

class GoogleMeetService
{
    private Client $client;
    private HttpClient $httpClient;

    /**
     * @return array<int, string>
     */
    public static function getOAuthScopes(): array
    {
        return [
            'https://www.googleapis.com/auth/meetings.space.created',
            'https://www.googleapis.com/auth/meetings.space.settings',
        ];
    }

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setApplicationName('Laravel Google Meet');
        $this->client->setScopes(self::getOAuthScopes());
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');

        $this->client->setClientId(config('services.google_calendar.client_id'));
        $this->client->setClientSecret(config('services.google_calendar.client_secret'));
        $this->client->setRedirectUri(config('services.google_calendar.redirect_uri'));

        $accessToken = config('services.google_calendar.access_token');
        if ($accessToken) {
            $this->client->setAccessToken($accessToken);

            if ($this->client->isAccessTokenExpired()) {
                $this->refreshAccessToken();
            }
        }

        $this->httpClient = new HttpClient([
            'base_uri' => 'https://meet.googleapis.com/',
            'http_errors' => true,
            'timeout' => 20,
        ]);
    }

    /**
     * @param array<int, string> $coOrganizers
        * @return array{success: bool, space_name: string|null, meet_link: string|null, meeting_code: string|null, errors: array<int, string>, details: array<string, mixed>}
     */
    public function configureMeetingByLink(?string $meetLink, array $coOrganizers = [], string $displayName = ''): array
    {
        $errors = [];
        $details = [];
        $coHostWarnings = [];

        if (!$meetLink) {
            return [
                'success' => false,
                'space_name' => null,
                'meet_link' => null,
                'meeting_code' => null,
                'errors' => ['No se encontro el enlace de Google Meet para configurar el space.'],
                'details' => [],
            ];
        }

        $meetingCode = $this->extractMeetingCodeFromUrl($meetLink);
        if (!$meetingCode) {
            return [
                'success' => false,
                'space_name' => null,
                'meet_link' => null,
                'meeting_code' => null,
                'errors' => ['No se pudo extraer el codigo de la reunion desde el enlace de Google Meet.'],
                'details' => ['meet_link' => $meetLink],
            ];
        }

        try {
            $space = $this->getSpaceByMeetingCode($meetingCode);
            $spaceName = $space['name'] ?? null;

            if (!$spaceName) {
                return [
                    'success' => false,
                    'space_name' => null,
                    'meet_link' => null,
                    'meeting_code' => null,
                    'errors' => ['Google Meet devolvio un space sin nombre.'],
                    'details' => ['space' => $space, 'meeting_code' => $meetingCode],
                ];
            }

            $details['meeting_code'] = $meetingCode;
            $details['space'] = $space;

            try {
                $details['updated_space'] = $this->updateSpaceConfig($spaceName, [
                    'accessType' => 'OPEN',
                    'entryPointAccess' => 'ALL',
                    'moderation' => 'ON',
                ], [
                    'config.accessType',
                    'config.entryPointAccess',
                    'config.moderation',
                ]);
            } catch (\Throwable $exception) {
                $errors[] = 'No se pudo cambiar el acceso de la reunion a ABIERTA: ' . $exception->getMessage();
            }

            if ($displayName !== '') {
                try {
                    $this->updateSpaceDisplayName($spaceName, $displayName);
                    $details['display_name_updated'] = $displayName;
                } catch (\Throwable $exception) {
                    Log::warning('No se pudo actualizar el displayName del espacio Meet.', [
                        'space_name' => $spaceName,
                        'display_name' => $displayName,
                        'error' => $exception->getMessage(),
                    ]);
                }
            }

            $coHostApiEnabled = (bool) config('services.google_calendar.enable_members_api');

            if (!empty($coOrganizers) && !$coHostApiEnabled) {
                $coHostWarnings[] = 'El endpoint de co-host no esta disponible en la API publica de Google Meet v2. La reunion queda ABIERTA, pero los co-organizadores deben asignarse manualmente en Meet.';
            }

            if (!empty($coOrganizers) && $coHostApiEnabled) {
                $coHostResults = [];
                foreach ($coOrganizers as $email) {
                    try {
                        $coHostResults[$email] = $this->addCoHost($spaceName, $email);
                    } catch (\Throwable $exception) {
                        $message = 'No se pudo agregar como co-host a ' . $email . ': ' . $exception->getMessage();

                        if ($this->isMethodUnavailableMessage($exception->getMessage())) {
                            $coHostWarnings[] = $message;
                        } else {
                            $errors[] = $message;
                        }
                    }
                }

                if (!empty($coHostResults)) {
                    $details['cohosts'] = $coHostResults;
                }
            }

            if (!empty($coHostWarnings)) {
                $details['cohost_warnings'] = $coHostWarnings;
                Log::info('Google Meet configurado con acceso ABIERTO; co-host automatico no disponible en este entorno/API.', [
                    'space_name' => $spaceName,
                    'warnings' => $coHostWarnings,
                ]);
            }

            return [
                'success' => count($errors) === 0,
                'space_name' => $spaceName,
                'meet_link' => $space['meetingUri'] ?? $meetLink,
                'meeting_code' => $space['meetingCode'] ?? $meetingCode,
                'errors' => $errors,
                'details' => $details,
            ];
        } catch (\Throwable $exception) {
            return [
                'success' => false,
                'space_name' => null,
                'meet_link' => null,
                'meeting_code' => null,
                'errors' => ['No se pudo resolver/configurar el space de Google Meet: ' . $exception->getMessage()],
                'details' => ['meeting_code' => $meetingCode],
            ];
        }
    }

    /**
     * @param array<int, string> $coOrganizers
     * @return array{success: bool, space_name: string|null, meet_link: string|null, meeting_code: string|null, errors: array<int, string>, details: array<string, mixed>}
     */
    public function ensureSharedMeeting(?string $existingMeetLink, array $coOrganizers = [], string $displayName = ''): array
    {
        if ($existingMeetLink) {
            return $this->configureMeetingByLink($existingMeetLink, $coOrganizers, $displayName);
        }

        $createdSpace = $this->createSpace($displayName);
        $meetLink = $createdSpace['meetingUri'] ?? null;

        if (!$meetLink) {
            return [
                'success' => false,
                'space_name' => $createdSpace['name'] ?? null,
                'meet_link' => null,
                'meeting_code' => null,
                'errors' => ['Google Meet no devolvio meetingUri al crear el espacio compartido del modulo.'],
                'details' => ['space' => $createdSpace],
            ];
        }

        $configured = $this->configureMeetingByLink($meetLink, $coOrganizers, $displayName);
        $configured['details']['created_space'] = $createdSpace;

        return $configured;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSpaceByMeetingCode(string $meetingCode): array
    {
        return $this->requestJson('GET', 'v2/spaces/' . rawurlencode($meetingCode));
    }

    /**
     * @return array<string, mixed>
     */
    public function createSpace(string $displayName = ''): array
    {
        $body = [
            'config' => [
                'accessType' => 'OPEN',
                'entryPointAccess' => 'ALL',
                'moderation' => 'ON',
            ],
        ];

        if ($displayName !== '') {
            $body['displayName'] = $displayName;
        }

        return $this->requestJson('POST', 'v2/spaces', ['json' => $body]);
    }

    /**
     * @return array<string, mixed>
     */
    public function updateSpaceDisplayName(string $spaceName, string $displayName): array
    {
        return $this->requestJson('PATCH', 'v2/' . ltrim($spaceName, '/'), [
            'query' => ['updateMask' => 'displayName'],
            'json' => [
                'name' => $spaceName,
                'displayName' => $displayName,
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $config
     * @param array<int, string> $updateMask
     * @return array<string, mixed>
     */
    public function updateSpaceConfig(string $spaceName, array $config, array $updateMask): array
    {
        return $this->requestJson('PATCH', 'v2/' . ltrim($spaceName, '/'), [
            'query' => [
                'updateMask' => implode(',', $updateMask),
            ],
            'json' => [
                'name' => $spaceName,
                'config' => $config,
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function addCoHost(string $spaceName, string $email): array
    {
        $normalizedEmail = strtolower(trim($email));
        $candidateRequests = [
            [
                'uri' => 'v2beta/' . ltrim($spaceName, '/') . '/members',
                'payload' => [
                    'email' => $normalizedEmail,
                    'role' => 'COHOST',
                ],
            ],
        ];

        $lastExceptionMessage = null;

        foreach ($candidateRequests as $candidate) {
            try {
                return $this->requestJson('POST', $candidate['uri'], [
                    'json' => $candidate['payload'],
                ]);
            } catch (\Throwable $exception) {
                $lastExceptionMessage = $exception->getMessage();

                if ($this->isMethodUnavailableMessage($lastExceptionMessage)) {
                    continue;
                }

                throw $exception;
            }
        }

        throw new \RuntimeException($lastExceptionMessage ?: 'No se pudo agregar co-host: metodo no disponible.');
    }

    public function extractMeetingCodeFromUrl(string $meetLink): ?string
    {
        $path = parse_url($meetLink, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            return null;
        }

        $meetingCode = trim($path, '/');

        return preg_match('/^[a-z]{3}-[a-z]{4}-[a-z]{3}$/i', $meetingCode) === 1
            ? strtolower($meetingCode)
            : null;
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function requestJson(string $method, string $uri, array $options = []): array
    {
        $accessToken = $this->getValidAccessToken();
        $options['headers'] = array_merge($options['headers'] ?? [], [
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/json',
        ]);

        try {
            $response = $this->httpClient->request($method, ltrim($uri, '/'), $options);
        } catch (RequestException $exception) {
            $body = $exception->hasResponse()
                ? (string) $exception->getResponse()->getBody()
                : $exception->getMessage();

            Log::warning('Error llamando a Google Meet API.', [
                'method' => $method,
                'uri' => $uri,
                'response' => $body,
            ]);

            throw new \RuntimeException($body, previous: $exception);
        } catch (GuzzleException $exception) {
            throw new \RuntimeException($exception->getMessage(), previous: $exception);
        }

        $decoded = json_decode((string) $response->getBody(), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function getValidAccessToken(): string
    {
        if ($this->client->isAccessTokenExpired()) {
            $this->refreshAccessToken();
        }

        $accessToken = $this->client->getAccessToken();
        $token = is_array($accessToken)
            ? ($accessToken['access_token'] ?? null)
            : $accessToken;

        if (!is_string($token) || $token === '') {
            throw new \RuntimeException('No hay un access token valido para Google Meet API. Reautoriza la cuenta de Google Calendar/Meet.');
        }

        return $token;
    }

    private function refreshAccessToken(): void
    {
        $refreshToken = config('services.google_calendar.refresh_token');

        if (!$refreshToken) {
            return;
        }

        $newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);

        if (isset($newToken['error'])) {
            throw new \RuntimeException((string) ($newToken['error_description'] ?? $newToken['error']));
        }

        Log::info('Token de Google Meet renovado.');
    }

    private function isMethodUnavailableMessage(string $message): bool
    {
        return str_contains($message, 'Method not found')
            || str_contains($message, '"status": "NOT_FOUND"')
            || str_contains($message, '"status":"NOT_FOUND"');
    }
}
