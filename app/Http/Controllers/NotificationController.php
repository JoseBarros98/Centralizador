<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Mostrar todas las notificaciones del usuario.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $notifications = $this->notificationService->getUserNotifications($user, 15);
        $stats = $this->notificationService->getUserNotificationStats($user);

        return view('notifications.index', compact('notifications', 'stats'));
    }

    /**
     * Obtener notificaciones no leídas (para dropdown/popup).
     */
    public function unread(Request $request): JsonResponse
    {
        $user = Auth::user();
        $limit = $request->get('limit', 4);
        
        $notifications = $this->notificationService->getUserUnreadNotifications($user, $limit);
        $unreadCount = $this->notificationService->getUnreadCount($user);

        return response()->json([
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->data['type'] ?? 'notification',
                    'title' => $notification->data['title'] ?? 'Notificación',
                    'message' => $notification->data['message'] ?? '',
                    'url' => $notification->data['url'] ?? '#',
                    'created_at' => $notification->created_at->diffForHumans(),
                    'priority' => $notification->data['priority'] ?? null,
                ];
            }),
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Marcar una notificación como leída.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = Auth::user();
        $success = $this->notificationService->markAsRead($id, $user);

        if ($success) {
            $unreadCount = $this->notificationService->getUnreadCount($user);
            
            return response()->json([
                'success' => true,
                'message' => 'Notificación marcada como leída',
                'unread_count' => $unreadCount,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se pudo marcar la notificación como leída',
        ], 404);
    }

    /**
     * Marcar todas las notificaciones como leídas.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = Auth::user();
        $markedCount = $this->notificationService->markAllAsRead($user);

        return response()->json([
            'success' => true,
            'message' => "Se marcaron {$markedCount} notificaciones como leídas",
            'marked_count' => $markedCount,
            'unread_count' => 0,
        ]);
    }

    /**
     * Eliminar una notificación.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = Auth::user();
        $success = $this->notificationService->deleteNotification($id, $user);

        if ($success) {
            $unreadCount = $this->notificationService->getUnreadCount($user);
            
            return response()->json([
                'success' => true,
                'message' => 'Notificación eliminada',
                'unread_count' => $unreadCount,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se pudo eliminar la notificación',
        ], 404);
    }

    /**
     * Obtener el conteo de notificaciones no leídas.
     */
    public function getUnreadCount(Request $request): JsonResponse
    {
        $user = Auth::user();
        $unreadCount = $this->notificationService->getUnreadCount($user);

        return response()->json([
            'unread_count' => $unreadCount,
        ]);
    }
}