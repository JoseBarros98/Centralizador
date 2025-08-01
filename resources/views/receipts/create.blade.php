<x-app-layout>
    @section('header-title', 'Subir Nuevo Recibo')
    
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('receipts.index', $inscription) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:border-gray-800 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Volver al Historial
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Información de la Inscripción</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Código:</span>
                            <span class="ml-2 text-sm text-gray-900">{{ $inscription->code }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Estudiante:</span>
                            <span class="ml-2 text-sm text-gray-900">{{ $inscription->first_name }} {{ $inscription->paternal_surname }} {{ $inscription->maternal_surname }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Programa:</span>
                            <span class="ml-2 text-sm text-gray-900">{{ $inscription->program->name }}</span>
                        </div>
                    </div>

                    <h3 class="text-lg font-medium text-gray-900 mb-4">Subir Recibo</h3>
                    <form action="{{ route('receipts.store', $inscription) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="receipt_file" class="block text-sm font-medium text-gray-700">Archivo del Recibo (JPG, PNG, PDF)</label>
                            <input type="file" name="receipt_file" id="receipt_file" class="mt-1 block w-full" accept=".jpg,.jpeg,.png,.pdf" required>
                            @error('receipt_file')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                                Subir Recibo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
