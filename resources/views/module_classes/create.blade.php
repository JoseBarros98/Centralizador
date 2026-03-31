<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-white leading-tight">
                {{ __('Crear Nueva Clase') }}
            </h2>
            <a href="{{ route('programs.modules.show', [$program->id, $module->id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-800 focus:outline-none focus:border-gray-800 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('Volver al Módulo') }}
            </a>
        </div>
    </x-slot>

    <div >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('programs.modules.classes.store', [$program->id, $module->id]) }}">
                        @csrf

                        @php
                            $defaultWeekday = $module->start_date?->dayOfWeek;
                            $selectedWeekdays = old('weekdays', $defaultWeekday !== null ? [$defaultWeekday] : []);
                            $weekdayOptions = [
                                1 => 'Lunes',
                                2 => 'Martes',
                                3 => 'Miercoles',
                                4 => 'Jueves',
                                5 => 'Viernes',
                                6 => 'Sabado',
                                0 => 'Domingo',
                            ];
                            $selectedScheduleType = old('schedule_type', 'recurring');
                        @endphp

                        <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <p class="text-sm font-semibold text-gray-900">Programacion de clases</p>
                            <p class="mt-1 text-sm text-gray-600">Define si vas a crear una sola clase o varias clases dentro del rango del modulo en dias especificos.</p>

                            <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 bg-white p-4">
                                    <input type="radio" name="schedule_type" value="single" class="mt-1 border-gray-300 text-indigo-600 focus:ring-indigo-500" @checked($selectedScheduleType === 'single')>
                                    <span>
                                        <span class="block text-sm font-medium text-gray-900">Una sola clase</span>
                                        <span class="block text-xs text-gray-500">Crea una sesion puntual con una fecha especifica.</span>
                                    </span>
                                </label>

                                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 bg-white p-4">
                                    <input type="radio" name="schedule_type" value="recurring" class="mt-1 border-gray-300 text-indigo-600 focus:ring-indigo-500" @checked($selectedScheduleType === 'recurring')>
                                    <span>
                                        <span class="block text-sm font-medium text-gray-900">Varias clases por rango</span>
                                        <span class="block text-xs text-gray-500">Genera automaticamente las clases entre inicio y fin solo en los dias seleccionados.</span>
                                    </span>
                                </label>
                            </div>

                            <x-input-error :messages="$errors->get('schedule_type')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div data-schedule-single>
                                <x-input-label for="class_date" :value="__('Fecha de la Clase')" />
                                <x-text-input id="class_date" class="block mt-1 w-full" type="date" name="class_date" :value="old('class_date')" required />
                                <x-input-error :messages="$errors->get('class_date')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2 rounded-lg border border-amber-200 bg-amber-50 p-4" data-schedule-recurring>
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div>
                                        <x-input-label for="range_start_date" :value="__('Fecha de Inicio del Rango')" />
                                        <x-text-input id="range_start_date" class="block mt-1 w-full" type="date" name="range_start_date" :value="old('range_start_date', $module->start_date?->format('Y-m-d'))" />
                                        <p class="mt-1 text-xs text-gray-500">Se usa como primera fecha posible para generar clases.</p>
                                        <x-input-error :messages="$errors->get('range_start_date')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="range_end_date" :value="__('Fecha de Fin del Rango')" />
                                        <x-text-input id="range_end_date" class="block mt-1 w-full" type="date" name="range_end_date" :value="old('range_end_date', $module->finalization_date?->format('Y-m-d'))" />
                                        <p class="mt-1 text-xs text-gray-500">Se usa como ultima fecha posible para generar clases.</p>
                                        <x-input-error :messages="$errors->get('range_end_date')" class="mt-2" />
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <x-input-label :value="__('Dias de clase')" />
                                    <p class="mt-1 text-xs text-gray-500">Marca los dias en los que realmente se dicta el modulo. Ejemplo: martes y jueves.</p>

                                    <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">
                                        @foreach($weekdayOptions as $weekdayValue => $weekdayLabel)
                                            <label class="flex items-center gap-2 rounded-md border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700">
                                                <input type="checkbox" name="weekdays[]" value="{{ $weekdayValue }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(in_array((string) $weekdayValue, array_map('strval', $selectedWeekdays), true))>
                                                <span>{{ $weekdayLabel }}</span>
                                            </label>
                                        @endforeach
                                    </div>

                                    <x-input-error :messages="$errors->get('weekdays')" class="mt-2" />
                                    <x-input-error :messages="$errors->get('weekdays.*')" class="mt-2" />
                                </div>
                            </div>

                            <div>
                                <x-input-label for="start_time" :value="__('Hora de Inicio')" />
                                <x-text-input id="start_time" class="block mt-1 w-full" type="time" name="start_time" :value="old('start_time')" required />
                                <x-input-error :messages="$errors->get('start_time')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="end_time" :value="__('Hora de Fin')" />
                                <x-text-input id="end_time" class="block mt-1 w-full" type="time" name="end_time" :value="old('end_time')" required />
                                <x-input-error :messages="$errors->get('end_time')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="class_link" :value="__('Enlace de la Clase (opcional)')" />
                                <x-text-input id="class_link" class="block mt-1 w-full" type="url" name="class_link" :value="old('class_link')" />
                                <x-input-error :messages="$errors->get('class_link')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2 border rounded-lg p-4 bg-blue-50">
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="create_google_meet" value="1" @checked(old('create_google_meet')) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                    <span class="text-sm font-medium text-gray-800">Crear o reutilizar un solo Google Meet para todo el módulo</span>
                                </label>
                                <p class="mt-2 text-xs text-gray-600">Si activas esta opción, el módulo usará un único enlace de Google Meet compartido por todas sus clases.</p>

                                <div class="mt-3">
                                    <x-input-label for="co_organizers" :value="__('Co-organizadores (correos, separados por coma)')" />
                                    <textarea id="co_organizers" name="co_organizers" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('co_organizers') }}</textarea>
                                    <x-input-error :messages="$errors->get('co_organizers')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button class="ml-3">
                                {{ __('Guardar Programacion') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const scheduleTypeInputs = document.querySelectorAll('input[name="schedule_type"]');
            const singleSection = document.querySelector('[data-schedule-single]');
            const recurringSection = document.querySelector('[data-schedule-recurring]');
            const classDateInput = document.getElementById('class_date');
            const rangeStartInput = document.getElementById('range_start_date');
            const rangeEndInput = document.getElementById('range_end_date');
            const weekdayInputs = document.querySelectorAll('input[name="weekdays[]"]');

            function toggleScheduleSections() {
                const selectedType = document.querySelector('input[name="schedule_type"]:checked')?.value || 'recurring';
                const isSingle = selectedType === 'single';

                singleSection.classList.toggle('hidden', !isSingle);
                recurringSection.classList.toggle('hidden', isSingle);

                classDateInput.required = isSingle;
                rangeStartInput.required = !isSingle;
                rangeEndInput.required = !isSingle;

                weekdayInputs.forEach(function (input) {
                    input.required = false;
                });
            }

            scheduleTypeInputs.forEach(function (input) {
                input.addEventListener('change', toggleScheduleSections);
            });

            toggleScheduleSections();
        });
    </script>
</x-app-layout>
