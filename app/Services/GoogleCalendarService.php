<?php

namespace App\Services;

use App\Models\Module;
use App\Models\ModuleClass;
use Carbon\Carbon;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\ConferenceData;
use Google\Service\Calendar\CreateConferenceRequest;
use Google\Service\Calendar\ConferenceSolutionKey;
use Google\Service\Calendar\EntryPoint;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventAttendee;
use Google\Service\Calendar\EventDateTime;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleCalendarService
{
    private Client $client;
    private Calendar $service;
    private string $calendarId;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setApplicationName('Laravel Google Calendar');
        $this->client->setScopes(array_merge([
            Calendar::CALENDAR_EVENTS,
        ], GoogleMeetService::getOAuthScopes()));
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

        $this->service = new Calendar($this->client);
        $this->calendarId = (string) config('services.google_calendar.calendar_id', 'primary');
    }

    public function upsertClassEvent(Module $module, ModuleClass $class, array $coOrganizers = []): Event
    {
        $event = $this->buildClassEvent($module, $class, $coOrganizers);

        if ($class->google_calendar_event_id) {
            $existingEvent = $this->service->events->get($this->calendarId, $class->google_calendar_event_id);

            // Preserve native Google Meet conferenceData when updating an existing event.
            if (!$event->getConferenceData() && $existingEvent->getConferenceData()) {
                $event->setConferenceData($existingEvent->getConferenceData());
            }

            return $this->service->events->patch(
                $this->calendarId,
                $class->google_calendar_event_id,
                $event,
                ['conferenceDataVersion' => 1]
            );
        }

        return $this->service->events->insert(
            $this->calendarId,
            $event,
            ['conferenceDataVersion' => 1]
        );
    }

    /**
     * @param array<int, ModuleClass> $classes
     */
    public function upsertRecurringModuleEvent(Module $module, array $classes, ?string $eventId = null, array $coOrganizers = []): Event
    {
        $orderedClasses = collect($classes)
            ->filter(static fn ($class) => $class instanceof ModuleClass)
            ->sortBy('class_date')
            ->values();

        if ($orderedClasses->isEmpty()) {
            throw new \InvalidArgumentException('No hay clases para construir el evento recurrente.');
        }

        /** @var ModuleClass $firstClass */
        $firstClass = $orderedClasses->first();
        /** @var ModuleClass $lastClass */
        $lastClass = $orderedClasses->last();

        $program = $module->program;

        $startDateTime = Carbon::parse(
            $firstClass->class_date->format('Y-m-d') . ' ' . $firstClass->start_time->format('H:i:s'),
            config('app.timezone')
        );

        $endDateTime = Carbon::parse(
            $firstClass->class_date->format('Y-m-d') . ' ' . $firstClass->end_time->format('H:i:s'),
            config('app.timezone')
        );

        $untilDateTime = Carbon::parse(
            $lastClass->class_date->format('Y-m-d') . ' ' . $firstClass->end_time->format('H:i:s'),
            config('app.timezone')
        )->utc()->format('Ymd\\THis\\Z');

        $byDays = $orderedClasses
            ->map(fn (ModuleClass $class) => $this->mapDayOfWeekToRRule($class->class_date->dayOfWeek))
            ->unique()
            ->values()
            ->all();

        $event = new Event([
            'summary' => $module->name . ' - ' . $program->name,
            'description' => $this->buildDescription($program->name, $module->name),
            'location' => $program->modality_name ?? null,
            'start' => new EventDateTime([
                'dateTime' => $startDateTime->toRfc3339String(),
                'timeZone' => config('app.timezone'),
            ]),
            'end' => new EventDateTime([
                'dateTime' => $endDateTime->toRfc3339String(),
                'timeZone' => config('app.timezone'),
            ]),
            'recurrence' => [
                'RRULE:FREQ=WEEKLY;BYDAY=' . implode(',', $byDays) . ';UNTIL=' . $untilDateTime,
            ],
            'attendees' => $this->buildAttendees($module, $coOrganizers),
            'guestsCanModify' => true,
            'guestsCanInviteOthers' => true,
            'guestsCanSeeOtherGuests' => true,
            'visibility' => 'public',
            'transparency' => 'opaque',
        ]);

        if ($eventId) {
            $existingEvent = $this->service->events->get($this->calendarId, $eventId);

            if ($existingEvent->getConferenceData()) {
                $event->setConferenceData($existingEvent->getConferenceData());
            } else {
                $event->setConferenceData($this->buildConferenceData());
            }

            return $this->service->events->patch(
                $this->calendarId,
                $eventId,
                $event,
                ['conferenceDataVersion' => 1]
            );
        }

        $event->setConferenceData($this->buildConferenceData());

        return $this->service->events->insert(
            $this->calendarId,
            $event,
            ['conferenceDataVersion' => 1]
        );
    }

    public function deleteClassEvent(ModuleClass $class): void
    {
        if (!$class->google_calendar_event_id) {
            return;
        }

        $this->service->events->delete($this->calendarId, $class->google_calendar_event_id);
    }

    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    /**
     * @return array<int, string>
     */
    public static function getRequestedScopes(): array
    {
        return array_merge([
            Calendar::CALENDAR_EVENTS,
        ], GoogleMeetService::getOAuthScopes());
    }

    /**
     * @return array<string, mixed>
     */
    public function handleAuthCallback(string $code): array
    {
        $this->client->fetchAccessTokenWithAuthCode($code);

        return $this->client->getAccessToken();
    }

    public function testConnection(): bool
    {
        $events = $this->service->events->listEvents($this->calendarId, [
            'maxResults' => 1,
            'singleEvents' => true,
            'orderBy' => 'startTime',
            'timeMin' => now()->subDay()->toRfc3339String(),
        ]);

        return $events !== null;
    }

    private function buildClassEvent(Module $module, ModuleClass $class, array $coOrganizers): Event
    {
        $program = $module->program;

        $startDateTime = Carbon::parse(
            $class->class_date->format('Y-m-d') . ' ' . $class->start_time->format('H:i:s'),
            config('app.timezone')
        );

        $endDateTime = Carbon::parse(
            $class->class_date->format('Y-m-d') . ' ' . $class->end_time->format('H:i:s'),
            config('app.timezone')
        );

        $sharedMeetLink = $class->google_meet_link ?: $class->class_link;
        $hasSharedMeet = $sharedMeetLink && str_contains((string) $sharedMeetLink, 'meet.google.com/');
        $isNewCalendarEvent = !$class->google_calendar_event_id;

        $event = new Event([
            'summary' => $module->name . ' - ' . $program->name,
            'description' => $this->buildDescription(
                $program->name,
                $module->name,
                (!$isNewCalendarEvent && $hasSharedMeet) ? (string) $sharedMeetLink : null
            ),
            'location' => (!$isNewCalendarEvent && $hasSharedMeet)
                ? (string) $sharedMeetLink
                : ($program->modality_name ?? null),
            'start' => new EventDateTime([
                'dateTime' => $startDateTime->toRfc3339String(),
                'timeZone' => config('app.timezone'),
            ]),
            'end' => new EventDateTime([
                'dateTime' => $endDateTime->toRfc3339String(),
                'timeZone' => config('app.timezone'),
            ]),
            'attendees' => $this->buildAttendees($module, $coOrganizers),
            // Permitir que invitados modifiquen el evento y vean a otros invitados (abierta)
            'guestsCanModify' => true,
            'guestsCanInviteOthers' => true,
            'guestsCanSeeOtherGuests' => true,
            'visibility' => 'public',
            'transparency' => 'opaque',
        ]);

        // Para eventos nuevos siempre solicitamos conferencia nativa de Calendar.
        // En actualizaciones, preservamos la conferencia existente en upsertClassEvent().
        if ($isNewCalendarEvent) {
            $event->setConferenceData($this->buildConferenceData());
        }

        return $event;
    }

    private function buildConferenceData(): ConferenceData
    {
        return new ConferenceData([
            'createRequest' => new CreateConferenceRequest([
                'requestId' => (string) Str::uuid(),
                'conferenceSolutionKey' => new ConferenceSolutionKey([
                    'type' => 'hangoutsMeet',
                ]),
            ]),
            'entryPoints' => [
                new EntryPoint([
                    'entryPointType' => 'more',
                    'uri' => '',
                    'label' => '',
                    'pin' => '',
                ]),
            ],
        ]);
    }

    /**
     * @return array<int, EventAttendee>
     */
    private function buildAttendees(Module $module, array $coOrganizers): array
    {
        $normalAttendees = array_filter([
            optional($module->teacher)->email,
            optional($module->monitor)->email,
        ]);

        $coOrganizerEmails = array_filter(array_map(static fn ($email) => strtolower(trim((string) $email)), $coOrganizers));

        $attendees = [];

        // Los co-organizadores se agregan como attendees aqui y luego se promueven a COHOST via Meet API.
        foreach ($coOrganizerEmails as $email) {
            $attendee = new EventAttendee(['email' => $email]);
            $attendee->setOptional(false);
            $attendee->setResponseStatus('accepted');
            $attendees[] = $attendee;
        }

        // Agregar asistentes normales
        foreach (array_unique(array_map(static fn ($email) => strtolower(trim((string) $email)), $normalAttendees)) as $email) {
            if (!in_array($email, $coOrganizerEmails, true)) {
                $attendees[] = new EventAttendee(['email' => $email]);
            }
        }

        return $attendees;
    }

    private function buildDescription(string $programName, string $moduleName, ?string $meetLink = null): string
    {
        $lines = [
            'Clase sincronizada desde CENTTEST.',
            'Programa: ' . $programName,
            'Modulo: ' . $moduleName,
        ];

        if ($meetLink) {
            $lines[] = '';
            $lines[] = 'Enlace Google Meet: ' . $meetLink;
        }

        return implode("\n", $lines);
    }

    private function mapDayOfWeekToRRule(int $dayOfWeek): string
    {
        return match ($dayOfWeek) {
            0 => 'SU',
            1 => 'MO',
            2 => 'TU',
            3 => 'WE',
            4 => 'TH',
            5 => 'FR',
            6 => 'SA',
            default => 'MO',
        };
    }

    private function refreshAccessToken(): void
    {
        $refreshToken = config('services.google_calendar.refresh_token');

        if (!$refreshToken) {
            return;
        }

        $this->client->refreshToken($refreshToken);

        Log::info('Token de Google Calendar renovado.');
    }
}
