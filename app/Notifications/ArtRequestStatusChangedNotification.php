<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ArtRequestStatusChangedNotification extends Notification
{
    use Queueable;

    public $artRequest;
    public $notificationType;
    public $oldStatus;
    public $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct($artRequest, ?string $oldStatus = null, ?string $newStatus = null, string $notificationType = 'art_request_status_changed')
    {
        $this->artRequest = $artRequest;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus ?? $artRequest->status;
        $this->notificationType = $notificationType;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $title = $this->artRequest->title ?? 'Sin título';
        $statusText = $this->getStatusText($this->newStatus);
        
        return (new MailMessage)
                    ->subject('Estado Actualizado - ' . $title)
                    ->greeting('¡Hola ' . $notifiable->name . '!')
                    ->line('El estado de la solicitud de arte ha sido actualizado.')
                    ->line('**Título:** ' . $title)
                    ->line('**Nuevo Estado:** ' . $statusText)
                    ->line('**Fecha de entrega:** ' . ($this->artRequest->delivery_date ? $this->artRequest->delivery_date->format('d/m/Y') : 'Por definir'))
                    ->action('Ver Solicitud', url('/art_requests/' . $this->artRequest->id))
                    ->line('¡Gracias por tu atención!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $title = $this->artRequest->title ?? 'Sin título';
        $statusText = $this->getStatusText($this->newStatus);
        
        return [
            'type' => $this->notificationType,
            'art_request_id' => $this->artRequest->id,
            'title' => 'Estado actualizado: ' . $title,
            'message' => "El estado de la solicitud '{$title}' ha cambiado a {$statusText}.",
            'url' => '/art_requests/' . $this->artRequest->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'delivery_date' => $this->artRequest->delivery_date?->format('Y-m-d'),
            'priority' => $this->artRequest->priority ?? 'MEDIA',
        ];
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
