<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Editar Solicitud de Arte') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('art_requests.show', $artRequest) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    Ver
                </a>
                <a href="{{ route('art_requests.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div >
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('art_requests.update', $artRequest) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Información Básica -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Información Básica</h3>
                        
                        <div class="space-y-6">
                            <!-- Título -->
                            <div>
                                <x-label for="title" :value="__('Título')" />
                                <x-input id="title" class="block mt-1 w-full @error('title') border-red-300 @enderror" 
                                         type="text" name="title" :value="old('title', $artRequest->title)" required />
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Descripción -->
                            <div>
                                <x-label for="description" :value="__('Descripción')" />
                                <textarea id="description" name="description" rows="4" 
                                          class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full @error('description')  @enderror" 
                                          required>{{ old('description', $artRequest->description) }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Contenido -->
                            <div>
                                <x-label for="content" :value="__('Contenido')" />
                                <textarea id="content" name="content" rows="4" 
                                          class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full @error('content')  @enderror" 
                                          required>{{ old('content', $artRequest->content) }}</textarea>
                                @error('content')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>


                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Tipo de Arte -->
                                <div>
                                    <x-label for="type_of_art_id" :value="__('Tipo de Arte')" />
                                    <select id="type_of_art_id" name="type_of_art_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full @error('type_of_art_id') @enderror" required>
                                        <option value="">Seleccionar tipo de arte</option>
                                        @foreach($typeOfArts as $typeOfArt)
                                            <option value="{{ $typeOfArt->id }}" {{ old('type_of_art_id', $artRequest->type_of_art_id) == $typeOfArt->id ? 'selected' : '' }}>
                                                {{ $typeOfArt->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('type_of_art_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Pilar de Contenido -->
                                <div>
                                    <x-label for="content_pillar_id" :value="__('Pilar de Contenido')" />
                                    <select id="content_pillar_id" name="content_pillar_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full @error('content_pillar_id')  @enderror">
                                        <option value="">Seleccionar pilar de contenido</option>
                                        @foreach($contentPillars as $contentPillar)
                                            <option value="{{ $contentPillar->id }}" {{ old('content_pillar_id', $artRequest->content_pillar_id) == $contentPillar->id ? 'selected' : '' }}>
                                                {{ $contentPillar->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('content_pillar_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Fecha de Solicitud -->
                                <div>
                                    <x-label for="request_date" :value="__('Fecha de Solicitud')" />
                                    <x-input id="request_date" class="block mt-1 w-full @error('request_date') border-red-300 @enderror" 
                                             type="date" name="request_date" :value="old('request_date', $artRequest->request_date->format('Y-m-d'))" required />
                                    @error('request_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Fecha de Entrega -->
                                <div>
                                    <x-label for="delivery_date" :value="__('Fecha de Entrega')" />
                                    <x-input id="delivery_date" class="block mt-1 w-full @error('delivery_date') border-red-300 @enderror" 
                                             type="date" name="delivery_date" :value="old('delivery_date', $artRequest->delivery_date->format('Y-m-d'))" required />
                                    @error('delivery_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Diseñador -->
                                <div>
                                    <x-label for="designer_id" :value="__('Diseñador Asignado')" />
                                    <select id="designer_id" name="designer_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full @error('designer_id')  @enderror">
                                        <option value="">Sin asignar</option>
                                        @foreach($designers as $designer)
                                            <option value="{{ $designer->id }}" {{ old('designer_id', $artRequest->designer_id) == $designer->id ? 'selected' : '' }}>
                                                {{ $designer->name }}
                                            </option>
                                        @endforeach
                                        @if($artRequest->designer && !$designers->pluck('id')->contains($artRequest->designer_id))
                                            <option value="{{ $artRequest->designer_id }}" selected>
                                                {{ $artRequest->designer->name }} (No disponible)
                                            </option>
                                        @endif
                                    </select>
                                    @error('designer_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Prioridad -->
                                <div>
                                    <x-label for="priority" :value="__('Prioridad')" />
                                    <select id="priority" name="priority" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full @error('priority') @enderror" required>
                                        <option value="">Seleccionar prioridad</option>
                                        <option value="BAJA" {{ old('priority', $artRequest->priority) == 'BAJA' ? 'selected' : '' }}>Baja</option>
                                        <option value="MEDIA" {{ old('priority', $artRequest->priority) == 'MEDIA' ? 'selected' : '' }}>Media</option>
                                        <option value="ALTA" {{ old('priority', $artRequest->priority) == 'ALTA' ? 'selected' : '' }}>Alta</option>
                                    </select>
                                    @error('priority')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <input type="hidden" name="status" value="{{ old('status', $artRequest->status) }}">

                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('art_requests.show', $artRequest) }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Cancelar') }}
                            </a>
                            <x-button>
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                {{ __('Actualizar Solicitud') }}
                            </x-button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
