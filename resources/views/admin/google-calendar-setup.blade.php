@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">
                <i class="fab fa-google text-blue-600 mr-2"></i>
                Configuracion de Google Calendar y Google Meet
            </h1>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-3">Estado de configuracion</h3>
                    <div class="flex items-center">
                        @if($isConfigured)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Configurado
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                No configurado
                            </span>
                        @endif
                    </div>

                    @if($isConfigured)
                        <div class="mt-4">
                            <button onclick="testConnection()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                                Probar conexion
                            </button>
                        </div>
                    @endif
                </div>

                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-3">Configuracion OAuth</h3>
                    <p class="text-gray-600 mb-4">
                        Esta configuracion usa la cuenta separada definida en las variables GOOGLE_CALENDAR_* para crear eventos, configurar Google Meet y administrar acceso/co-hosts.
                    </p>

                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <p><strong>1.</strong> Habilita las APIs de Google Calendar y Google Meet en Google Cloud Console</p>
                        <p><strong>2.</strong> Usa el redirect URI configurado para Calendar:</p>
                        <p class="bg-gray-100 px-2 py-1 rounded">{{ config('services.google_calendar.redirect_uri') }}</p>
                        <p><strong>3.</strong> Autoriza o reautoriza la cuenta que sera duena de las reuniones Meet</p>
                        <p><strong>4.</strong> Si ya habias autorizado antes, debes reautorizar para conceder los nuevos scopes de Google Meet</p>
                    </div>

                    <a href="{{ $authUrl }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                        {{ $isConfigured ? 'Reautorizar Google Calendar y Meet' : 'Autorizar Google Calendar y Meet' }}
                    </a>
                </div>
            </div>

            <div class="mt-6 bg-blue-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold mb-3 text-blue-900">Variables requeridas</h3>
                <div class="text-blue-800 space-y-2 text-sm">
                    <ul class="list-disc ml-6 space-y-1">
                        <li>GOOGLE_CALENDAR_CLIENT_ID</li>
                        <li>GOOGLE_CALENDAR_CLIENT_SECRET</li>
                        <li>GOOGLE_CALENDAR_REDIRECT_URI</li>
                        <li>GOOGLE_CALENDAR_ACCESS_TOKEN</li>
                        <li>GOOGLE_CALENDAR_REFRESH_TOKEN</li>
                        <li>GOOGLE_CALENDAR_ID</li>
                    </ul>
                </div>
            </div>

            <div class="mt-6 bg-yellow-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold mb-3 text-yellow-900">Scopes solicitados</h3>
                <ul class="list-disc ml-6 space-y-1 text-sm text-yellow-900">
                    @foreach($requestedScopes as $scope)
                        <li>{{ $scope }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function testConnection() {
    fetch('{{ route("admin.google-calendar.test") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        alert((data.success ? 'OK: ' : 'ERROR: ') + data.message);
    })
    .catch(error => {
        alert('ERROR: ' + error);
    });
}
</script>
@endsection
