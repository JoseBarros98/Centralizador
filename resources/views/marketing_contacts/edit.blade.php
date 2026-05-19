<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">Editar Contacto — {{ $marketingContact->nombre }}</h2>
    </x-slot>

    <div class="w-full sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <form method="POST" action="{{ route('marketing-contacts.update', $marketingContact) }}">
                    @csrf @method('PUT')
                    @include('marketing_contacts._form')

                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t">
                        <a href="{{ route('marketing-contacts.show', $marketingContact) }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm rounded-md hover:bg-gray-300">Cancelar</a>
                        <x-primary-button>Actualizar Contacto</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
