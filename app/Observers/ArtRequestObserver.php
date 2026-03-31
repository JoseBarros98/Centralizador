<?php

namespace App\Observers;

use App\Models\ArtRequest;
use App\Models\User;
use App\Models\Notification;
use App\Notifications\NewArtRequestNotification;
use App\Notifications\ArtRequestStatusChangedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ArtRequestObserver
{
    /**
     * Handle the ArtRequest "created" event.
     */
    public function created(ArtRequest $artRequest): void
    {
        $this->sendNewArtRequestNotifications($artRequest);
    }

    /**
     * Handle the ArtRequest "updated" event.
     */
    public function updated(ArtRequest $artRequest): void
    {
        // Verificar si el estado cambió
        if ($artRequest->isDirty('status')) {
            $oldStatus = $artRequest->getOriginal('status');
            $newStatus = $artRequest->status;
            
            $this->sendStatusChangedNotifications($artRequest, $oldStatus, $newStatus);
        }
    }

    /**
     * Enviar notificaciones cuando se crea una nueva solicitud de arte.
     */
    private function sendNewArtRequestNotifications(ArtRequest $artRequest): void
    {
        try {
            // Obtener el diseñador asignado
            $designer = $artRequest->designer;
            
            // Obtener administradores (usuarios con rol admin)
            $administrators = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['admin', 'administrador', 'Administrator']);
            })->get();

            // Crear notificaciones para el diseñador si está asignado
            if ($designer) {
                $this->createNotification($designer, $artRequest, 'designer');
                
                // También enviar notificación por email/sistema de Laravel
                $designer->notify(new NewArtRequestNotification($artRequest, 'new_art_request_designer'));
            }

            // Crear notificaciones para todos los administradores
            foreach ($administrators as $admin) {
                // Evitar enviar notificación doble si el admin es también el diseñador
                if (!$designer || $admin->id !== $designer->id) {
                    $this->createNotification($admin, $artRequest, 'admin');
                    
                    // También enviar notificación por email/sistema de Laravel
                    $admin->notify(new NewArtRequestNotification($artRequest, 'new_art_request_admin'));
                }
            }

            Log::info('Notificaciones enviadas para ArtRequest creado', [
                'art_request_id' => $artRequest->id,
                'designer_id' => $designer?->id,
                'admin_count' => $administrators->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error al enviar notificaciones para ArtRequest', [
                'art_request_id' => $artRequest->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Crear una notificación en la base de datos.
     */
    private function createNotification(User $user, ArtRequest $artRequest, string $recipientType): void
    {
        $requesterName = $artRequest->requester->name ?? 'Usuario';
        $title = $artRequest->title ?? 'Nueva Solicitud de Arte';

        $message = $recipientType === 'admin' 
            ? "Se ha creado una nueva solicitud de arte por {$requesterName}: {$title}"
            : "Se te ha asignado una nueva solicitud de arte: {$title}";

        Notification::create([
            'uuid' => Str::uuid(),
            'type' => 'App\\Notifications\\NewArtRequestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'type' => 'new_art_request',
                'art_request_id' => $artRequest->id,
                'title' => $title,
                'message' => $message,
                'requester_name' => $requesterName,
                'priority' => $artRequest->priority ?? 'MEDIA',
                'delivery_date' => $artRequest->delivery_date?->format('Y-m-d'),
                'recipient_type' => $recipientType,
                'url' => '/art_requests/' . $artRequest->id,
            ],
        ]);
    }

    /**
     * Enviar notificaciones cuando se cambia el estado de una solicitud de arte.
     */
    private function sendStatusChangedNotifications(ArtRequest $artRequest, ?string $oldStatus, string $newStatus): void
    {
        try {
            // Obtener usuarios que deben ser notificados
            $usersToNotify = collect();

            // 1. Notificar al solicitante (requester)
            if ($artRequest->requester) {
                $usersToNotify->push($artRequest->requester);
            }

            // 2. Notificar al diseñador asignado
            if ($artRequest->designer) {
                $usersToNotify->push($artRequest->designer);
            }

            // 3. Notificar a los administradores
            $administrators = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['admin', 'administrador', 'Administrator']);
            })->get();

            $usersToNotify = $usersToNotify->merge($administrators)->unique('id');

            // Enviar notificaciones
            foreach ($usersToNotify as $user) {
                $user->notify(new ArtRequestStatusChangedNotification($artRequest, $oldStatus, $newStatus));
            }

            Log::info('Notificaciones de cambio de estado enviadas', [
                'art_request_id' => $artRequest->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'notified_users' => $usersToNotify->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error al enviar notificaciones de cambio de estado', [
                'art_request_id' => $artRequest->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Crear una notificación de cambio de estado en la base de datos.
     */
    private function createStatusChangeNotification(User $user, ArtRequest $artRequest, ?string $oldStatus, string $newStatus): void
    {
        $title = $artRequest->title ?? 'Sin título';
        $statusText = $this->getStatusText($newStatus);
        $oldStatusText = $oldStatus ? $this->getStatusText($oldStatus) : null;

        $message = $oldStatusText
            ? "El estado de la solicitud '{$title}' ha cambiado de {$oldStatusText} a {$statusText}."
            : "El estado de la solicitud '{$title}' ha cambiado a {$statusText}.";

        Notification::create([
            'uuid' => Str::uuid(),
            'type' => 'App\\Notifications\\ArtRequestStatusChangedNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => [
                'type' => 'art_request_status_changed',
                'art_request_id' => $artRequest->id,
                'title' => 'Estado actualizado: ' . $title,
                'message' => $message,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'priority' => $artRequest->priority ?? 'MEDIA',
                'delivery_date' => $artRequest->delivery_date?->format('Y-m-d'),
                'url' => '/art_requests/' . $artRequest->id,
            ],
        ]);
    }

    /**
     * Obtener el texto amigable del estado.
     */
    private function getStatusText(string $status): string
    {
        $statusMap = [
            'pending' => 'Pendiente',
            'in_progress' => 'En Progreso',
            'review' => 'En Revisión',
            'completed' => 'Completado',
            'cancelled' => 'Cancelado',
            'on_hold' => 'En Espera',
        ];

        return $statusMap[$status] ?? ucfirst($status);
    }
}