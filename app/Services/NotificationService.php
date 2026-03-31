<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationService
{
    /**
     * Obtener notificaciones de un usuario con paginación.
     */
    public function getUserNotifications(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return $user->notifications()
                   ->latest()
                   ->paginate($perPage);
    }

    /**
     * Obtener notificaciones no leídas de un usuario.
     */
    public function getUserUnreadNotifications(User $user, int|null $limit = null): Collection
    {
        $query = $user->unreadNotifications()->latest();
        
        if ($limit) {
            $query = $query->limit($limit);
        }
        
        return $query->get();
    }

    /**
     * Marcar una notificación como leída.
     */
    public function markAsRead(string $notificationId, User $user): bool
    {
        $notification = $user->notifications()->where('id', $notificationId)->first();
        
        if ($notification && $notification->isUnread()) {
            $notification->markAsRead();
            return true;
        }
        
        return false;
    }

    /**
     * Marcar todas las notificaciones de un usuario como leídas.
     */
    public function markAllAsRead(User $user): int
    {
        return $user->unreadNotifications()->update(['read_at' => now()]);
    }

    /**
     * Eliminar una notificación.
     */
    public function deleteNotification(string $notificationId, User $user): bool
    {
        $notification = $user->notifications()->where('id', $notificationId)->first();
        
        if ($notification) {
            $notification->delete();
            return true;
        }
        
        return false;
    }

    /**
     * Obtener el conteo de notificaciones no leídas.
     */
    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    /**
     * Limpiar notificaciones antiguas (más de X días).
     */
    public function cleanOldNotifications(int $daysOld = 30): int
    {
        return Notification::where('created_at', '<', now()->subDays($daysOld))->delete();
    }

    /**
     * Obtener estadísticas de notificaciones para un usuario.
     */
    public function getUserNotificationStats(User $user): array
    {
        $total = $user->notifications()->count();
        $unread = $user->unreadNotifications()->count();
        $read = $total - $unread;

        // Notificaciones por tipo en los últimos 7 días
        $recentByType = $user->notifications()
                            ->where('created_at', '>=', now()->subDays(7))
                            ->get()
                            ->groupBy(function ($notification) {
                                return $notification->data['type'] ?? 'unknown';
                            })
                            ->map(function ($group) {
                                return $group->count();
                            });

        return [
            'total' => $total,
            'unread' => $unread,
            'read' => $read,
            'recent_by_type' => $recentByType,
        ];
    }
}