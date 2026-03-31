<?php

namespace App\Notifications;

use App\Models\Inscription;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InscriptionChecklistUpdatedNotification extends Notification
{
    use Queueable;

    public $inscription;
    public $field;
    public $value;
    public $updatedBy;
    public $checklistType; // 'document' o 'access'

    /**
     * Create a new notification instance.
     */
    public function __construct(Inscription $inscription, string $field, bool $value, User $updatedBy, string $checklistType)
    {
        $this->inscription = $inscription;
        $this->field = $field;
        $this->value = $value;
        $this->updatedBy = $updatedBy;
        $this->checklistType = $checklistType;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $fieldName = $this->getFieldName($this->field);
        $action = $this->value ? 'marcó' : 'desmarcó';
        $checklistName = $this->checklistType === 'document' ? 'Documentos' : 'Accesos';
        
        return [
            'type' => 'inscription_checklist_updated',
            'inscription_id' => $this->inscription->id,
            'inscription_code' => $this->inscription->code,
            'student_name' => $this->inscription->getFullName(),
            'program_name' => $this->inscription->program->name,
            'field' => $this->field,
            'field_name' => $fieldName,
            'value' => $this->value,
            'action' => $action,
            'checklist_type' => $this->checklistType,
            'checklist_name' => $checklistName,
            'updated_by_name' => $this->updatedBy->name,
            'updated_by_id' => $this->updatedBy->id,
            'title' => "Checklist Actualizado - {$this->inscription->code}",
            'message' => "{$this->updatedBy->name} {$action} '{$fieldName}' en {$checklistName} de {$this->inscription->getFullName()}",
            'url' => '/inscriptions/' . $this->inscription->id,
        ];
    }

    /**
     * Get the human-readable field name.
     */
    private function getFieldName(string $field): string
    {
        $fieldNames = [
            // Documentos
            'has_identity_card' => 'Cédula de identidad',
            'has_degree_title' => 'Título en provisión nacional',
            'has_academic_diploma' => 'Diploma de grado académico',
            'has_birth_certificate' => 'Certificado de nacimiento',
            'has_commitment_letter' => 'Carta de compromiso',
            // Accesos
            'was_added_to_the_group' => 'Se añadió al grupo',
            'accesses_were_sent' => 'Se enviaron accesos',
            'mail_was_sent' => 'Se envió correo',
        ];

        return $fieldNames[$field] ?? $field;
    }
}
