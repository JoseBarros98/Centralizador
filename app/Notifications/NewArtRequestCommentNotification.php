<?php

namespace App\Notifications;

use App\Models\ArtRequest;
use App\Models\ArtRequestComment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewArtRequestCommentNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ArtRequestComment $comment,
        public ArtRequest $artRequest,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $commenter = $this->comment->user->name;
        $title     = $this->artRequest->title;

        return [
            'type'           => 'new_art_request_comment',
            'art_request_id' => $this->artRequest->id,
            'title'          => "Nuevo comentario en \"{$title}\"",
            'message'        => "{$commenter} comentó en \"{$title}\"",
            'commenter'      => $commenter,
            'url'            => '/art_requests/' . $this->artRequest->id . '#comments-section',
        ];
    }
}
