<?php

namespace App\Http\Controllers;

use App\Models\ArtRequest;
use App\Models\ArtRequestComment;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ArtRequestCommentController extends Controller
{
    public function store(Request $request, ArtRequest $artRequest)
    {
        $user = Auth::user();
        if (!$user instanceof \App\Models\User) abort(403);
        if (!($user->can('content.view') || $user->can('content.view_own'))) abort(403);

        $request->validate([
            'body'        => 'required|string|min:1|max:2000',
            'is_internal' => 'boolean',
            'attachment'  => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,mp4,avi,mov,zip,rar|max:51200',
        ]);

        $isInternal = $request->boolean('is_internal') && $user->can('content.view');

        $attachmentName    = null;
        $attachmentDriveId = null;
        $attachmentType    = null;

        if ($request->hasFile('attachment')) {
            $file          = $request->file('attachment');
            $drive         = new GoogleDriveService();
            $designerName  = $artRequest->designer  ? $artRequest->designer->name  : 'Sin Asignar';
            $requesterName = $artRequest->requester  ? $artRequest->requester->name : 'Desconocido';

            $artFolderId      = $drive->createHierarchicalFolder(
                'Solicitudes de Arte', $designerName, $requesterName, $artRequest->title
            );
            $commentsFolderId = $drive->findOrCreateSubfolder('Comentarios', $artFolderId);

            $driveResult = $drive->uploadFile(
                $file->getRealPath(),
                $file->getClientOriginalName(),
                $file->getClientMimeType(),
                null,
                $commentsFolderId
            );

            $attachmentName    = $file->getClientOriginalName();
            $attachmentDriveId = $driveResult['id'];
            $attachmentType    = $file->getClientMimeType();
        }

        $comment = ArtRequestComment::create([
            'art_request_id'     => $artRequest->id,
            'user_id'            => $user->id,
            'body'               => $request->body,
            'is_internal'        => $isInternal,
            'attachment_name'    => $attachmentName,
            'attachment_drive_id'=> $attachmentDriveId,
            'attachment_type'    => $attachmentType,
        ]);

        $comment->load('user');

        if ($request->expectsJson()) {
            return response()->json([
                'id'              => $comment->id,
                'body'            => e($comment->body),
                'is_internal'     => $comment->is_internal,
                'user'            => $comment->user->name,
                'avatar'          => strtoupper(substr($comment->user->name, 0, 1)),
                'created_at'      => $comment->created_at->format('d/m/Y H:i'),
                'is_own'          => true,
                'attachment_name' => $comment->attachment_name,
                'attachment_url'  => $comment->attachment_drive_id
                    ? route('art-requests.comments.attachment', [$artRequest, $comment])
                    : null,
            ]);
        }

        return back()->with('success', 'Comentario agregado.');
    }

    public function serveAttachment(ArtRequest $artRequest, ArtRequestComment $comment)
    {
        $user = Auth::user();
        if (!$user instanceof \App\Models\User) abort(403);
        if (!($user->can('content.view') || $user->can('content.view_own'))) abort(403);

        if (!$comment->attachment_drive_id) abort(404);

        $content = (new GoogleDriveService())->downloadFile($comment->attachment_drive_id);

        return response($content, 200, [
            'Content-Type'        => $comment->attachment_type ?? 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . $comment->attachment_name . '"',
        ]);
    }

    public function destroy(ArtRequest $artRequest, ArtRequestComment $comment)
    {
        $user = Auth::user();

        if ($comment->user_id !== $user->id && !$user->can('content.edit')) {
            abort(403);
        }

        if ($comment->attachment_drive_id) {
            try {
                (new GoogleDriveService())->deleteFile($comment->attachment_drive_id);
            } catch (\Exception $e) {
                Log::warning('No se pudo eliminar adjunto de Drive: ' . $e->getMessage());
            }
        }

        $comment->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Comentario eliminado.');
    }
}
