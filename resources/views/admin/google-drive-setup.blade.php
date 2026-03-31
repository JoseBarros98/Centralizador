@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">
                <i class="fab fa-google-drive text-blue-600 mr-2"></i>
                Configuración de Google Drive
            </h1>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('tokens'))
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6">
                    <p class="font-bold">Tokens obtenidos. Agrega estas variables a tu archivo .env:</p>
                    <div class="mt-2 bg-gray-800 text-green-400 p-3 rounded font-mono text-sm">
                        @foreach(session('tokens') as $key => $value)
                            <div>{{ strtoupper($key) }}={{ $value }}</div>
                        @endforeach
                    </div>
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
                <!-- Estado de configuración -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-3">Estado de configuración</h3>
                    <div class="flex items-center">
                        @if($isConfigured)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>
                                Configurado
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-1"></i>
                                No configurado
                            </span>
                        @endif
                    </div>

                    @if($isConfigured)
                        <div class="mt-4">
                            <button onclick="testConnection()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                                <i class="fas fa-network-wired mr-1"></i>
                                Probar conexión
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Configuración inicial -->
                @if(!$isConfigured)
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-3">Configuración inicial</h3>
                    <p class="text-gray-600 mb-4">
                        Para configurar Google Drive, necesitas crear un proyecto en Google Cloud Console y obtener credenciales OAuth 2.0.
                    </p>
                    
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        <p><strong>1.</strong> Ve a <a href="https://console.cloud.google.com" target="_blank" class="text-blue-600 hover:underline">Google Cloud Console</a></p>
                        <p><strong>2.</strong> Crea un nuevo proyecto o selecciona uno existente</p>
                        <p><strong>3.</strong> Habilita la API de Google Drive</p>
                        <p><strong>4.</strong> Crea credenciales OAuth 2.0</p>
                        <p><strong>5.</strong> Agrega esta URL de redirección: <code class="bg-gray-200 px-1 rounded">{{ config('services.google.redirect_uri') }}</code></p>
                    </div>

                    @if($authUrl)
                        <a href="{{ $authUrl }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                            <i class="fab fa-google mr-2"></i>
                            Autorizar con Google
                        </a>
                    @endif
                </div>
                @endif

                <!-- Herramientas -->
                @if($isConfigured)
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold mb-3">Herramientas</h3>
                    
                    <div class="space-y-3">
                        <div>
                            <label for="folder_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Crear carpeta principal
                            </label>
                            <div class="flex">
                                <input type="text" id="folder_name" name="folder_name" 
                                       class="flex-1 border border-gray-300 rounded-l-lg px-3 py-2 text-sm"
                                       placeholder="Nombre de la carpeta" value="Laravel Files">
                                <button onclick="createMainFolder()" 
                                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-r-lg text-sm">
                                    <i class="fas fa-folder-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Instrucciones -->
            <div class="mt-6 bg-blue-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold mb-3 text-blue-900">
                    <i class="fas fa-info-circle mr-1"></i>
                    Instrucciones de configuración
                </h3>
                <div class="text-blue-800 space-y-2 text-sm">
                    <p><strong>Variables de entorno requeridas:</strong></p>
                    <ul class="list-disc ml-6 space-y-1">
                        <li><code>GOOGLE_CLIENT_ID</code> - ID del cliente OAuth</li>
                        <li><code>GOOGLE_CLIENT_SECRET</code> - Secreto del cliente OAuth</li>
                        <li><code>GOOGLE_REDIRECT_URI</code> - URL de redirección (ya configurada)</li>
                        <li><code>GOOGLE_ACCESS_TOKEN</code> - Token de acceso (se obtiene automáticamente)</li>
                        <li><code>GOOGLE_REFRESH_TOKEN</code> - Token de actualización (se obtiene automáticamente)</li>
                        <li><code>GOOGLE_DRIVE_FOLDER_ID</code> - ID de la carpeta principal (opcional)</li>
                    </ul>
                    <p class="mt-3"><strong>Meet/Calendar con otra cuenta:</strong> usa variables <code>GOOGLE_CALENDAR_*</code> para mantener Calendar separado de Drive.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testConnection() {
    fetch('{{ route("admin.google-drive.test") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Error de conexión: ' + error);
    });
}

function createMainFolder() {
    const folderName = document.getElementById('folder_name').value;
    if (!folderName) {
        alert('Por favor ingresa un nombre para la carpeta');
        return;
    }

    fetch('{{ route("admin.google-drive.create-folder") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ folder_name: folderName })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Carpeta creada correctamente\nID: ' + data.folder.id + '\nNombre: ' + data.folder.name);
            console.log('Folder data:', data.folder);
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error);
    });
}
</script>
@endsection
