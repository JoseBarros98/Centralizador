<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Editar Inscripción') }}
        </h2>
    </x-slot>

    <div x-data="{}">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('inscriptions.update', $inscription) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Campos ocultos para datos sincronizados (los campos disabled no se envían en el form) -->
                        @if($inscription->is_synced)
                            <input type="hidden" name="full_name" value="{{ $inscription->full_name }}">
                            <input type="hidden" name="ci" value="{{ $inscription->ci }}">
                            <input type="hidden" name="birth_date" value="{{ $inscription->birth_date ? $inscription->birth_date->format('Y-m-d') : '' }}">
                            <input type="hidden" name="email" value="{{ $inscription->email }}">
                            <input type="hidden" name="phone" value="{{ $inscription->phone }}">
                            <input type="hidden" name="profession_id" value="{{ $inscription->profession_id }}">
                            <input type="hidden" name="program_id" value="{{ $inscription->program_id }}">
                            <input type="hidden" name="inscription_date" value="{{ $inscription->inscription_date->format('Y-m-d') }}">
                            <input type="hidden" name="payment_plan" value="{{ $inscription->payment_plan }}">
                            <input type="hidden" name="status" value="{{ $inscription->status }}">
                        @endif

                        <!-- DATOS PERSONALES -->
                        <div class="mb-8">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-indigo-700">Datos Personales</h3>
                                @if($inscription->is_synced)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Sincronizado desde DB Externa
                                    </span>
                                @endif
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Nombre Completo -->
                                <div class="md:col-span-3">
                                    <x-label for="full_name" :value="__('Nombre Completo')" />
                                    @if($inscription->is_synced)
                                        <div class="mt-1 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                            <p class="text-gray-900 font-medium">{{ $inscription->getFullName() }}</p>
                                            <p class="text-xs text-blue-600 mt-1">🔒 Campo sincronizado desde la base de datos externa</p>
                                        </div>
                                    @else
                                        <x-input id="full_name" class="block mt-1 w-full" type="text" name="full_name" :value="old('full_name', $inscription->full_name)" required autofocus />
                                        @error('full_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                    @endif
                                </div>

                                <!-- Fecha de Nacimiento -->
                                <div>
                                    <x-label for="birth_date" :value="__('Fecha de Nacimiento')" />
                                    @if($inscription->is_synced)
                                        <div class="mt-1 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                            <p class="text-gray-900">{{ $inscription->birth_date ? $inscription->birth_date->format('d/m/Y') : 'N/A' }}</p>
                                            <p class="text-xs text-blue-600 mt-1">🔒 Sincronizado</p>
                                        </div>
                                    @else
                                        <x-input id="birth_date" class="block mt-1 w-full" type="date" name="birth_date" :value="old('birth_date', $inscription->birth_date ? $inscription->birth_date->format('Y-m-d') : '')" />
                                        @error('birth_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                    @endif
                                </div>

                                <!-- CI -->
                                <div>
                                    <x-label for="ci" :value="__('CI')" />
                                    @if($inscription->is_synced)
                                        <div class="mt-1 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                            <p class="text-gray-900">{{ $inscription->ci }}</p>
                                            <p class="text-xs text-blue-600 mt-1">🔒 Sincronizado</p>
                                        </div>
                                    @else
                                        <x-input id="ci" class="block mt-1 w-full" type="text" name="ci" :value="old('ci', $inscription->ci)" required />
                                        @error('ci')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                    @endif
                                </div>

                                <!-- Género -->
                                <div>
                                    <x-label for="gender" :value="__('Género')" />
                                    <select id="gender" name="gender" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                        <option value="">Seleccionar género</option>
                                        <option value="Femenino" {{ old('gender', $inscription->gender) == 'Femenino' ? 'selected' : '' }}>Femenino</option>
                                        <option value="Masculino" {{ old('gender', $inscription->gender) == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                    </select>
                                    @error('gender')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>

                                <!-- Correo Electrónico -->
                                <div>
                                    <x-label for="email" :value="__('Correo Electrónico')" />
                                    @if($inscription->is_synced)
                                        <div class="mt-1 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                            <p class="text-gray-900">{{ $inscription->email }}</p>
                                            <p class="text-xs text-blue-600 mt-1">🔒 Sincronizado</p>
                                        </div>
                                    @else
                                        <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $inscription->email)" required />
                                        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                    @endif
                                </div>

                                <!-- Teléfono -->
                                <div>
                                    <x-label for="phone" :value="__('Teléfono')" />
                                    @if($inscription->is_synced)
                                        <div class="mt-1 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                            <p class="text-gray-900">{{ $inscription->phone }}</p>
                                            <p class="text-xs text-blue-600 mt-1">🔒 Sincronizado</p>
                                        </div>
                                    @else
                                        <x-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone', $inscription->phone)" required />
                                        @error('phone')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                    @endif
                                </div>

                                <!-- Estado Civil -->
                                <div>
                                    <x-label for="civil_status" :value="__('Estado Civil')" />
                                    <select id="civil_status" name="civil_status" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                        <option value="">Seleccionar estado civil</option>
                                        <option value="Soltero" {{ old('civil_status', $inscription->civil_status) == 'Soltero' ? 'selected' : '' }}>Soltero</option>
                                        <option value="Casado" {{ old('civil_status', $inscription->civil_status) == 'Casado' ? 'selected' : '' }}>Casado</option>
                                        <option value="Divorciado" {{ old('civil_status', $inscription->civil_status) == 'Divorciado' ? 'selected' : '' }}>Divorciado</option>
                                        <option value="Viudo" {{ old('civil_status', $inscription->civil_status) == 'Viudo' ? 'selected' : '' }}>Viudo</option>
                                    </select>
                                    @error('civil_status')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>

                                <!-- Profesión -->
                                <div class="relative">
                                    <x-label for="profession" :value="__('Profesión')" />
                                    @if($inscription->is_synced)
                                        <div class="mt-1 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                            <p class="text-gray-900">{{ $inscription->profession->name ?? 'N/A' }}</p>
                                            <p class="text-xs text-blue-600 mt-1">🔒 Sincronizado</p>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-between mb-1">
                                            <button type="button" onclick="openCreateProfessionModal()" 
                                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                                Nueva Profesión
                                            </button>
                                        </div>
                                        <input type="text" id="profession-search" placeholder="Buscar profesión..." 
                                               class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                               autocomplete="off" value="{{ old('profession_id') ? ($professions[old('profession_id')] ?? '') : ($inscription->profession_id ? ($professions[$inscription->profession_id] ?? '') : '') }}">
                                        <input type="hidden" id="profession" name="profession_id" value="{{ old('profession_id', $inscription->profession_id) }}">
                                        <div id="profession-results" class="absolute z-10 w-full bg-white shadow-lg rounded-md mt-1 max-h-60 overflow-auto hidden"></div>
                                        @error('profession_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                    @endif
                                </div>

                                <!-- Universidad -->
                                <div class="relative">
                                    <div class="flex items-center justify-between mb-1">
                                        <x-label for="university" :value="__('Universidad')" />
                                        <button type="button" onclick="openCreateUniversityModal()" 
                                                class="text-xs text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            Nueva Universidad
                                        </button>
                                    </div>
                                    <input type="text" id="university-search" placeholder="Buscar universidad..." 
                                           class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                           autocomplete="off" value="{{ old('university_id') ? ($universities[old('university_id')] ?? '') : ($inscription->university_id ? ($universities[$inscription->university_id] ?? '') : '') }}">
                                    <input type="hidden" id="university" name="university_id" value="{{ old('university_id', $inscription->university_id) }}">
                                    <div id="university-results" class="absolute z-10 w-full bg-white shadow-lg rounded-md mt-1 max-h-60 overflow-auto hidden"></div>
                                    @error('university_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>

                                <!-- Residencia -->
                                <div>
                                    <x-label for="residence" :value="__('Departamento de Residencia')" />
                                    <select id="residence" name="residence" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                        <option value="">Seleccionar departamento</option>
                                        <option value="La Paz" {{ old('residence', $inscription->residence) == 'La Paz' ? 'selected' : '' }}>La Paz</option>
                                        <option value="Cochabamba" {{ old('residence', $inscription->residence) == 'Cochabamba' ? 'selected' : '' }}>Cochabamba</option>
                                        <option value="Santa Cruz" {{ old('residence', $inscription->residence) == 'Santa Cruz' ? 'selected' : '' }}>Santa Cruz</option>
                                        <option value="Oruro" {{ old('residence', $inscription->residence) == 'Oruro' ? 'selected' : '' }}>Oruro</option>
                                        <option value="Potosí" {{ old('residence', $inscription->residence) == 'Potosí' ? 'selected' : '' }}>Potosí</option>
                                        <option value="Tarija" {{ old('residence', $inscription->residence) == 'Tarija' ? 'selected' : '' }}>Tarija</option>
                                        <option value="Chuquisaca" {{ old('residence', $inscription->residence) == 'Chuquisaca' ? 'selected' : '' }}>Chuquisaca</option>
                                        <option value="Beni" {{ old('residence', $inscription->residence) == 'Beni' ? 'selected' : '' }}>Beni</option>
                                        <option value="Pando" {{ old('residence', $inscription->residence) == 'Pando' ? 'selected' : '' }}>Pando</option>
                                    </select>
                                    @error('residence')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- DATOS DEL PROGRAMA -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-indigo-700 mb-4">Datos del Programa</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Programa -->
                                <div>
                                    <x-label for="program_id" :value="__('Programa')" />
                                    @if($inscription->is_synced)
                                        <div class="mt-1 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                            <p class="text-gray-900">{{ $inscription->program->name ?? 'N/A' }}</p>
                                            <p class="text-xs text-blue-600 mt-1">🔒 Sincronizado</p>
                                        </div>
                                    @else
                                        <select id="program_id" name="program_id" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                            <option value="">Seleccionar programa</option>
                                            @foreach($programs as $program)
                                                <option value="{{ $program->id }}" {{ old('program_id', $inscription->program_id) == $program->id ? 'selected' : '' }}>{{ $program->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('program_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                    @endif
                                </div>
                                
                                <div>
                                    <x-label for="location" :value="__('Sede')" />
                                    <x-input id="location" class="block mt-1 w-full" type="text" name="location" :value="old('location', $inscription->location)" required />
                                    @error('location')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                
                                <!-- Fecha de Inscripción -->
                                <div>
                                    <x-label for="inscription_date" :value="__('Fecha de Inscripción')" />
                                    @if($inscription->is_synced)
                                        <div class="mt-1 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                            <p class="text-gray-900">{{ $inscription->inscription_date->format('d/m/Y') }}</p>
                                            <p class="text-xs text-blue-600 mt-1">🔒 Sincronizado</p>
                                        </div>
                                    @else
                                        <x-input id="inscription_date" class="block mt-1 w-full" type="date" name="inscription_date" :value="old('inscription_date', $inscription->inscription_date->format('Y-m-d'))" required />
                                        @error('inscription_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                    @endif
                                </div>
                                
                                <div>
                                    <x-label for="certification" :value="__('Certificación')" />
                                    <x-input id="certification" class="block mt-1 w-full" type="text" name="certification" :value="old('certification', $inscription->certification)" required />
                                    @error('certification')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                
                                <!-- Información del Asesor -->
                                @if($inscription->external_advisor_name)
                                <div class="md:col-span-3 bg-blue-50 p-4 rounded-md">
                                    <h4 class="text-sm font-medium text-blue-900 mb-2">Información del Asesor</h4>
                                    <p class="text-sm text-blue-700">
                                        <strong>Asesor Externo:</strong> {{ $inscription->external_advisor_name }}
                                        @if($inscription->creator->email === 'sistema.externo@centtest.local')
                                            <span class="ml-2 text-xs text-blue-600">(No tiene cuenta en el sistema)</span>
                                        @else
                                            <span class="ml-2 text-xs text-green-600">(Vinculado a: {{ $inscription->creator->name }})</span>
                                        @endif
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- DATOS DE PAGO -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-indigo-700 mb-4">Datos de Pago</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Plan de Pago -->
                                <div>
                                    <x-label for="payment_plan" :value="__('Plan de Pago')" />
                                    @if($inscription->is_synced)
                                        <div class="mt-1 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                            <p class="text-gray-900">{{ $inscription->payment_plan ?? 'N/A' }}</p>
                                            <p class="text-xs text-blue-600 mt-1">🔒 Sincronizado</p>
                                        </div>
                                    @else
                                        <x-input id="payment_plan" class="block mt-1 w-full" type="text" name="payment_plan" :value="old('payment_plan', $inscription->payment_plan)" />
                                        <p class="text-xs text-gray-500 mt-1">Ej: Contado, Crédito, Descuento por Planilla, etc.</p>
                                        @error('payment_plan')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                    @endif
                                </div>
                                
                                <div>
                                    <x-label for="payment_method" :value="__('Medio de Pago')" />
                                    <select id="payment_method" name="payment_method" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                        <option value="Efectivo" {{ old('payment_method', $inscription->payment_method) == 'Efectivo' ? 'selected' : '' }}>Efectivo</option>
                                        <option value="QR" {{ old('payment_method', $inscription->payment_method) == 'QR' ? 'selected' : '' }}>QR</option>
                                        <option value="Deposito" {{ old('payment_method', $inscription->payment_method) == 'Deposito' ? 'selected' : '' }}>Depósito</option>
                                        <option value="Transferencia" {{ old('payment_method', $inscription->payment_method) == 'Transferencia' ? 'selected' : '' }}>Transferencia</option>
                                    </select>
                                    @error('payment_method')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                
                                <!-- Estado de Pago -->
                                @if($inscription->is_synced)
                                    <!-- Para inscripciones sincronizadas, mostrar estado académico externo y estado de pago local -->
                                    <div>
                                        <x-label for="external_status" :value="__('Estado Académico (Externo)')" />
                                        <div class="mt-1 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                            <p class="text-gray-900">{{ $inscription->external_inscription_status ?? 'N/A' }}</p>
                                            <p class="text-xs text-blue-600 mt-1">🔒 Sincronizado desde DB Externa</p>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <x-label for="local_payment_status" :value="__('Estado de Pago (Local)')" />
                                        <select id="local_payment_status" name="local_payment_status" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                            <option value="Pendiente" {{ old('local_payment_status', $inscription->local_payment_status) == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                            <option value="Adelanto" {{ old('local_payment_status', $inscription->local_payment_status) == 'Adelanto' ? 'selected' : '' }}>Adelanto</option>
                                            <option value="Completando" {{ old('local_payment_status', $inscription->local_payment_status) == 'Completando' ? 'selected' : '' }}>Completando</option>
                                            <option value="Completo" {{ old('local_payment_status', $inscription->local_payment_status) == 'Completo' ? 'selected' : '' }}>Completo</option>
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">Control de pagos local (editable)</p>
                                        @error('local_payment_status')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                    </div>

                                    <div>
                                        <x-label for="status_date" :value="__('Fecha del Cambio de Estado')" />
                                        <x-input id="status_date" class="block mt-1 w-full" type="date" name="status_date" :value="old('status_date', now()->toDateString())" />
                                        <p class="text-xs text-gray-500 mt-1">Fecha en que se registró el cambio (ej: si cambió en junio, poner fecha de junio)</p>
                                        @error('status_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                    </div>
                                @else
                                    <!-- Para inscripciones locales (no sincronizadas), usar status como antes -->
                                    <div class="md:col-span-2">
                                        <x-label for="status" :value="__('Estado')" />
                                        <select id="status" name="status" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">
                                            <option value="Completo" {{ old('status', $inscription->status) == 'Completo' ? 'selected' : '' }}>Completo</option>
                                            <option value="Completando" {{ old('status', $inscription->status) == 'Completando' ? 'selected' : '' }}>Completando</option>
                                            <option value="Adelanto" {{ old('status', $inscription->status) == 'Adelanto' ? 'selected' : '' }}>Adelanto</option>
                                        </select>
                                        @error('status')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                    </div>
                                @endif
                                
                                <div>
                                    <x-label for="enrollment_fee" :value="__('Matrícula (Bs)')" />
                                    <x-input id="enrollment_fee" class="block mt-1 w-full" type="number" step="0.01" name="enrollment_fee" :value="old('enrollment_fee', $inscription->enrollment_fee)" required />
                                    @error('enrollment_fee')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="first_installment" :value="__('Primera Cuota (Bs)')" />
                                    <x-input id="first_installment" class="block mt-1 w-full" type="number" step="0.01" name="first_installment" :value="old('first_installment', $inscription->first_installment)" required />
                                    @error('first_installment')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-label for="total_to_pay" :value="__('Total por pagar (Bs)')" />
                                    <x-input id="total_to_pay" class="block mt-1 w-full bg-gray-100" type="number" step="0.01" readonly :value="old('total_to_pay', $inscription->enrollment_fee + $inscription->first_installment)" />
                                    <p class="text-xs text-gray-500 mt-1">Campo calculado (Matrícula + Primera Cuota)</p>
                                </div>
                                <div>
                                    <x-label for="total_paid" :value="__('Total Pagado (Bs)')" />
                                    <x-input id="total_paid" class="block mt-1 w-full" type="number" step="0.01" name="total_paid" :value="old('total_paid', $inscription->total_paid)" required />
                                    @error('total_paid')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- DOCUMENTOS -->
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-indigo-700 mb-4">Archivos</h3>
                            @if($inscription->documents->count() > 0)
                                <div class="mt-3">
                                    <button type="button"
                                            class="inline-flex items-center px-4 py-2 bg-green-100 border border-transparent rounded-md font-semibold text-xs text-green-800 uppercase tracking-widest hover:bg-green-200 active:bg-green-300 focus:outline-none focus:border-green-300 focus:ring ring-green-200 disabled:opacity-25 transition ease-in-out duration-150"
                                            @click="$dispatch('open-modal', 'documents-modal')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        VER {{ $inscription->documents->count() }} DOCUMENTO(S)
                                    </button>
                                </div>
                            @endif
                            <div id="file-container">
                                <div class="file-group mb-2">
                                    <div class="flex items-center space-x-2">
                                        <select name="document_types[]" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            <option value="">Tipo de documento</option>
                                            <option value="ci">Cédula de Identidad</option>
                                            <option value="titulo">Título en Provisión Nacional</option>
                                            <option value="diploma">Diploma de Grado Académico</option>
                                            <option value="nacimiento">Certificado de Nacimiento</option>
                                            <option value="documentacion_completa">Documentación Completa</option>
                                            <option value="compromiso">Carta de Compromiso</option>
                                            <option value="congelamiento">Carta de Congelamiento</option>
                                            <option value="recibo">Recibo</option>
                                            <option value="factura">Factura</option>
                                            <option value="comprobante_pago">Comprobante de Pago</option>
                                        </select>
                                        <input type="file" name="document_files[]" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block shadow-sm sm:text-sm border-gray-300 rounded-md" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
                                        <button type="button" class="remove-file px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600" style="display: none;">Eliminar</button>
                                    </div>
                                    <input type="text" name="document_descriptions[]" placeholder="Descripción del archivo" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                            </div>
                            <button type="button" id="add-file" class="mt-2 inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Añadir archivo
                            </button>
                            @error('document_files')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- NOTAS -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-indigo-700 mb-4">Notas</h3>
                            <div>
                                <textarea id="notes" name="notes" rows="3" class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full">{{ old('notes', $inscription->notes) }}</textarea>
                                @error('notes')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('inscriptions.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                Cancelar
                            </a>
                            <x-button>
                                {{ __('Actualizar') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal para ver documentos -->
        <x-modal id="documents-modal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Documentos de {{ $inscription->full_name }}
                </h2>

                @if($inscription->documents->count() > 0)
                    <div class="mb-4">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($inscription->documents as $document)
                                        <tr>
                                            <td class="px-4 py-2 align-top">
                                                <span class="font-semibold text-indigo-700">{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</span>
                                            </td>
                                            <td class="px-4 py-2 align-top">
                                                <span class="text-gray-700">{{ $document->description }}</span>
                                            </td>
                                            <td class="px-4 py-2 align-top">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('documents.serve', $document) }}" target="_blank" class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700" title="Ver">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                    </a>
                                                    <form method="POST" action="{{ route('documents.destroy', ['inscription' => $inscription->id, 'document' => $document->id]) }}" onsubmit="return confirm('¿Eliminar este documento?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700" title="Eliminar">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <p class="text-gray-500 text-sm">No hay documentos asociados a esta inscripción.</p>
                @endif

                <div class="mt-6 flex justify-end">
                    <button type="button" @click="$dispatch('close')" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400">
                        Cerrar
                    </button>
                </div>
            </div>
        </x-modal>

        <!-- Modal para crear nueva profesión -->
        <x-modal id="create-profession-modal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Nueva Profesión</h2>
                <div class="mb-4">
                    <x-label for="new-profession-name" :value="__('Nombre de la Profesión')" />
                    <x-input id="new-profession-name" class="block mt-1 w-full" type="text" required autofocus />
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeModal('create-profession-modal')" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 mr-2">
                        Cancelar
                    </button>
                    <button type="button" id="create-profession-btn" onclick="createProfession()" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        Guardar
                    </button>
                </div>
            </div>
        </x-modal>

        <!-- Modal para crear nueva universidad -->
        <x-modal id="create-university-modal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Nueva Universidad</h2>
                <div class="mb-4">
                    <x-label for="new-university-initials" :value="__('Siglas')" />
                    <x-input id="new-university-initials" class="block mt-1 w-full" type="text" required autofocus />
                </div>
                <div class="mb-4">
                    <x-label for="new-university-name" :value="__('Nombre')" />
                    <x-input id="new-university-name" class="block mt-1 w-full" type="text" required />
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeModal('create-university-modal')" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 mr-2">
                        Cancelar
                    </button>
                    <button type="button" id="create-university-btn" onclick="createUniversity()" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                        Guardar
                    </button>
                </div>
            </div>
        </x-modal>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Búsqueda de profesiones y universidades
        setupSearchField('profession', '/api/inscriptions/search-professions');
        setupSearchField('university', '/api/inscriptions/search-universities');

        function setupSearchField(fieldName, apiUrl) {
            const searchInput = document.getElementById(`${fieldName}-search`);
            const hiddenInput = document.getElementById(fieldName);
            const resultsContainer = document.getElementById(`${fieldName}-results`);
            
            if (!searchInput || !hiddenInput || !resultsContainer) return;

            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                if (query.length < 2) {
                    resultsContainer.classList.add('hidden');
                    return;
                }

                searchTimeout = setTimeout(() => {
                    fetch(`${apiUrl}?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.length === 0) {
                                resultsContainer.innerHTML = '<div class="p-2 text-gray-500 text-sm">No se encontraron resultados</div>';
                                resultsContainer.classList.remove('hidden');
                                return;
                            }

                            let html = '<ul class="divide-y divide-gray-200">';
                            data.forEach(item => {
                                const displayText = fieldName === 'university' && item.initials ? `${item.initials} - ${item.name}` : item.name;
                                html += `<li class="p-2 hover:bg-indigo-50 cursor-pointer text-sm" data-id="${item.id}">${displayText}</li>`;
                            });
                            html += '</ul>';

                            resultsContainer.innerHTML = html;
                            resultsContainer.classList.remove('hidden');

                            resultsContainer.querySelectorAll('li').forEach(li => {
                                li.addEventListener('click', function() {
                                    hiddenInput.value = this.getAttribute('data-id');
                                    searchInput.value = this.textContent.trim();
                                    resultsContainer.classList.add('hidden');
                                });
                            });
                        });
                }, 300);
            });
        }

        // Cálculo del total a pagar
        const enrollmentFeeInput = document.getElementById('enrollment_fee');
        const firstInstallmentInput = document.getElementById('first_installment');
        const totalToPayInput = document.getElementById('total_to_pay');
        
        function calculateTotalToPay() {
            const enrollmentFee = parseFloat(enrollmentFeeInput.value) || 0;
            const firstInstallment = parseFloat(firstInstallmentInput.value) || 0;
            totalToPayInput.value = (enrollmentFee + firstInstallment).toFixed(2);
        }
        
        calculateTotalToPay();
        enrollmentFeeInput.addEventListener('input', calculateTotalToPay);
        firstInstallmentInput.addEventListener('input', calculateTotalToPay);

        // Gestión de archivos
        const fileContainer = document.getElementById('file-container');
        const addFileButton = document.getElementById('add-file');

        addFileButton.addEventListener('click', function() {
            const fileGroup = document.createElement('div');
            fileGroup.className = 'file-group mb-2';
            fileGroup.innerHTML = `
                <div class="flex items-center space-x-2">
                    <select name="document_types[]" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block shadow-sm sm:text-sm border-gray-300 rounded-md" required>
                        <option value="">Tipo de documento</option>
                        <option value="ci">Cédula de Identidad</option>
                        <option value="titulo">Título en Provisión Nacional</option>
                        <option value="diploma">Diploma de Grado Académico</option>
                        <option value="nacimiento">Certificado de Nacimiento</option>
                        <option value="documentacion_completa">Documentación Completa</option>
                        <option value="compromiso">Carta de Compromiso</option>
                        <option value="congelamiento">Carta de Congelamiento</option>
                        <option value="recibo">Recibo</option>
                        <option value="factura">Factura</option>
                        <option value="comprobante_pago">Comprobante de Pago</option>
                    </select>
                    <input type="file" name="document_files[]" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block shadow-sm sm:text-sm border-gray-300 rounded-md" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" required>
                    <button type="button" class="remove-file px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">Eliminar</button>
                </div>
                <input type="text" name="document_descriptions[]" placeholder="Descripción del archivo" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            `;
            fileContainer.appendChild(fileGroup);
            updateRemoveButtons();
            fileGroup.querySelector('.remove-file').addEventListener('click', function() {
                fileGroup.remove();
                updateRemoveButtons();
            });
        });

        function updateRemoveButtons() {
            const fileGroups = document.querySelectorAll('.file-group');
            fileGroups.forEach(group => {
                const removeButton = group.querySelector('.remove-file');
                removeButton.style.display = fileGroups.length > 1 ? 'block' : 'none';
            });
        }

        updateRemoveButtons();
    });

    function openCreateProfessionModal() {
        document.getElementById('new-profession-name').value = '';
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-profession-modal' }));
    }

    function openCreateUniversityModal() {
        document.getElementById('new-university-initials').value = '';
        document.getElementById('new-university-name').value = '';
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-university-modal' }));
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        window.dispatchEvent(new CustomEvent('close-modal', { detail: modalId }));
        if (modal) {
            modal.dispatchEvent(new CustomEvent('close'));
        }
    }

    function createProfession() {
        const name = document.getElementById('new-profession-name').value.trim();
        if (!name) {
            alert('Por favor ingrese el nombre de la profesión');
            return;
        }

        const submitBtn = document.getElementById('create-profession-btn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Guardando...';

        fetch('/professions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ name: name })
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Error al crear la profesión');
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                document.getElementById('profession-search').value = data.profession.name;
                document.getElementById('profession').value = data.profession.id;
                closeModal('create-profession-modal');
                alert('Profesión creada exitosamente');
            } else {
                alert('Error al crear la profesión');
            }
        })
        .catch(error => {
            alert(error.message || 'Error al crear la profesión');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Guardar';
        });
    }

    function createUniversity() {
        const initials = document.getElementById('new-university-initials').value.trim();
        const name = document.getElementById('new-university-name').value.trim();
        
        if (!initials || !name) {
            alert('Por favor complete todos los campos');
            return;
        }

        const submitBtn = document.getElementById('create-university-btn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Guardando...';

        fetch('/universities', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ initials: initials, name: name })
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Error al crear la universidad');
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                document.getElementById('university-search').value = data.university.initials + ' - ' + data.university.name;
                document.getElementById('university').value = data.university.id;
                closeModal('create-university-modal');
                alert('Universidad creada exitosamente');
            } else {
                alert('Error al crear la universidad');
            }
        })
        .catch(error => {
            alert(error.message || 'Error al crear la universidad');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Guardar';
        });
    }
    </script>
</x-app-layout>
