<?php

namespace App\Http\Controllers;

use App\Models\ArtRequest;
use App\Models\ArtRequestComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        ]);

        // Solo quienes pueden ver todos pueden marcar como interno
        $isInternal = $request->boolean('is_internal') && $user->can('content.view');

        $comment = ArtRequestComment::create([
            'art_request_id' => $artRequest->id,
            'user_id'        => $user->id,
            'body'           => $request->body,
            'is_internal'    => $isInternal,
        ]);

        $comment->load('user');

        if ($request->expectsJson()) {
            return response()->json([
                'id'          => $comment->id,
                'body'        => e($comment->body),
                'is_internal' => $comment->is_internal,
                'user'        => $comment->user->name,
                'avatar'      => strtoupper(substr($comment->user->name, 0, 1)),
                'created_at'  => $comment->created_at->format('d/m/Y H:i'),
                'is_own'      => true,
            ]);
        }

        return back()->with('success', 'Comentario agregado.');
    }

    public function destroy(ArtRequest $artRequest, ArtRequestComment $comment)
    {
        $user = Auth::user();

        // Puede borrar el propio comentario o si tiene permiso de edición total
        if ($comment->user_id !== $user->id && !$user->can('content.edit')) {
            abort(403);
        }

        $comment->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Comentario eliminado.');
    }
}
