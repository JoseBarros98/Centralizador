<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Prueba de Carga de Archivos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Formulario de Prueba</h3>
                    
                    <form id="testForm" action="{{ route('test.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label for="test_file" class="block text-sm font-medium text-gray-700">Archivo de Prueba</label>
                            <input type="file" class="mt-1 block w-full" id="test_file" name="test_file">
                        </div>
                        
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Probar Carga
                        </button>
                    </form>
                    
                    <div id="result" class="mt-4 p-4 bg-gray-100 rounded hidden">
                        <h4 class="font-medium text-gray-800">Resultado:</h4>
                        <pre id="resultContent" class="mt-2 text-sm"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('testForm');
        const result = document.getElementById('result');
        const resultContent = document.getElementById('resultContent');
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                result.classList.remove('hidden');
                resultContent.textContent = JSON.stringify(data, null, 2);
            })
            .catch(error => {
                result.classList.remove('hidden');
                resultContent.textContent = 'Error: ' + error.message;
            });
        });
    });
    </script>
</x-app-layout>
