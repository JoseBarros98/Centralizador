@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Notificaciones</h1>
                    <p class="mt-2 text-gray-600">Administra todas tus notificaciones</p>
                </div>
                <div class="flex space-x-3">
                    <button id="mark-all-read" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-check-double mr-2"></i>
                        Marcar todas como leídas
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <i class="fas fa-bell text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <i class="fas fa-bell-slash text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">No leídas</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['unread'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-check text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Leídas</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['read'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Todas las notificaciones</h2>
            </div>
            
            <div class="divide-y divide-gray-200">
                @forelse($notifications as $notification)
                    <div class="notification-item p-6 hover:bg-gray-50 {{ $notification->isUnread() ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}" 
                         data-notification-id="{{ $notification->id }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    @php
                                        $data = $notification->data;
                                        $iconClass = match($data['type'] ?? 'notification') {
                                            'new_art_request' => 'fas fa-palette text-purple-600',
                                            'art_request_updated' => 'fas fa-edit text-blue-600',
                                            'art_request_completed' => 'fas fa-check-circle text-green-600',
                                            default => 'fas fa-bell text-gray-600'
                                        };
                                    @endphp
                                    <i class="{{ $iconClass }} mr-3"></i>
                                    <h3 class="text-sm font-medium text-gray-900">
                                        {{ $data['title'] ?? 'Notificación' }}
                                    </h3>
                                    @if($notification->isUnread())
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Nueva
                                        </span>
                                    @endif
                                </div>
                                
                                <p class="text-sm text-gray-600 mb-2">
                                    {{ $data['message'] ?? 'Sin mensaje' }}
                                </p>
                                
                                <div class="flex items-center text-xs text-gray-500 space-x-4">
                                    <span>
                                        <i class="far fa-calendar mr-1"></i>
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                    @if(isset($data['priority']))
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                            {{ match($data['priority']) {
                                                'ALTA' => 'bg-red-100 text-red-800',
                                                'MEDIA' => 'bg-yellow-100 text-yellow-800',
                                                'BAJA' => 'bg-green-100 text-green-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            } }}">
                                            {{ $data['priority'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-2 ml-4">
                                @if(isset($data['url']) && $data['url'] !== '#')
                                    <a href="{{ $data['url'] }}" 
                                       class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                        Ver detalles
                                    </a>
                                @endif
                                
                                @if($notification->isUnread())
                                    <button class="mark-as-read-btn text-green-600 hover:text-green-900 text-sm" 
                                            data-notification-id="{{ $notification->id }}">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                                
                                <button class="delete-notification-btn text-red-600 hover:text-red-900 text-sm" 
                                        data-notification-id="{{ $notification->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center">
                        <i class="fas fa-bell-slash text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-500">No tienes notificaciones</p>
                    </div>
                @endforelse
            </div>
            
            @if($notifications->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Marcar todas como leídas
    document.getElementById('mark-all-read').addEventListener('click', function() {
        fetch('{{ route("notifications.mark-all-read") }}', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    });

    // Marcar individual como leída
    document.querySelectorAll('.mark-as-read-btn').forEach(button => {
        button.addEventListener('click', function() {
            const notificationId = this.dataset.notificationId;
            
            fetch(`/notifications/${notificationId}/read`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const item = document.querySelector(`[data-notification-id="${notificationId}"]`);
                    item.classList.remove('bg-blue-50', 'border-l-4', 'border-blue-500');
                    this.remove();
                    
                    // Actualizar badge si existe
                    updateNotificationBadge(data.unread_count);
                }
            });
        });
    });

    // Eliminar notificación
    document.querySelectorAll('.delete-notification-btn').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('¿Estás seguro de que quieres eliminar esta notificación?')) {
                const notificationId = this.dataset.notificationId;
                
                fetch(`/notifications/${notificationId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const item = document.querySelector(`[data-notification-id="${notificationId}"]`);
                        item.remove();
                        
                        // Actualizar badge si existe
                        updateNotificationBadge(data.unread_count);
                    }
                });
            }
        });
    });

    // Función para actualizar el badge de notificaciones
    function updateNotificationBadge(count) {
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'inline';
            } else {
                badge.style.display = 'none';
            }
        }
    }
});
</script>
@endpush
@endsection