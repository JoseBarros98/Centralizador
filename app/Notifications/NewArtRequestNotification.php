<?php

namespace App\Notifications;

use App\Models\ArtRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewArtRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $artRequest;
    public $notificationType;

    /**
     * Create a new notification instance.
     */
    public function __construct(ArtRequest $artRequest, string $notificationType = 'new_art_request')
    {
        $this->artRequest = $artRequest;
        $this->notificationType = $notificationType;
    }

    /**
     * Get the notification's delivery channels.
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
        $requesterName = $this->artRequest->requester->name ?? 'Usuario';
        $title = $this->artRequest->title ?? 'Sin título';
        
        return (new MailMessage)
                    ->subject('Nueva Solicitud de Arte - ' . $title)
                    ->greeting('¡Hola ' . $notifiable->name . '!')
                    ->line('Se ha creado una nueva solicitud de arte que requiere tu atención.')
                    ->line('**Solicitante:** ' . $requesterName)
                    ->line('**Título:** ' . $title)
                    ->line('**Descripción:** ' . ($this->artRequest->description ?? 'Sin descripción'))
                    ->line('**Fecha de entrega:** ' . ($this->artRequest->delivery_date ? $this->artRequest->delivery_date->format('d/m/Y') : 'Por definir'))
                    ->line('**Prioridad:** ' . ($this->artRequest->priority ?? 'MEDIA'))
                    ->action('Ver Solicitud', url('/art_requests/' . $this->artRequest->id))
                    ->line('¡Gracias por tu atención!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->notificationType,
            'art_request_id' => $this->artRequest->id,
            'title' => $this->artRequest->title ?? 'Nueva Solicitud de Arte',
            'message' => $this->getNotificationMessage($notifiable),
            'requester_name' => $this->artRequest->requester->name ?? 'Usuario',
            'priority' => $this->artRequest->priority ?? 'MEDIA',
            'delivery_date' => $this->artRequest->delivery_date?->format('Y-m-d'),
            'url' => '/art_requests/' . $this->artRequest->id,
        ];
    }

    /**
     * Get the notification message based on the recipient type.
     */
    private function getNotificationMessage(object $notifiable): string
    {
        $requesterName = $this->artRequest->requester->name ?? 'Un usuario';
        $title = $this->artRequest->title ?? 'una nueva solicitud de arte';

        if ($notifiable->isAdmin()) {
            return "Se ha creado una nueva solicitud de arte por {$requesterName}: {$title}";
        }

        // Para el diseñador
        return "Se te ha asignado una nueva solicitud de arte: {$title}";
    }
}