<div {{ $attributes->merge(['class' => 'md:grid md:grid-cols-3 md:gap-6']) }}>
    <div class="md:col-span-1">
        <div class="px-4 sm:px-0">
            <h3 class="text-lg font-medium leading-6 text-gray-900">{{ $title }}</h3>
            
            @if(isset($description))
                <p class="mt-1 text-sm text-gray-600">
                    {{ $description }}
                </p>
            @endif
        </div>
    </div>
    
    <div class="mt-5 md:mt-0 md:col-span-2">
        <div class="shadow overflow-hidden sm:rounded-md">
            <div class="px-4 py-5 bg-white sm:p-6">
                {{ $slot }}
            </div>
            
            @if(isset($actions))
                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                    {{ $actions }}
                </div>
            @endif
        </div>
    </div>
</div>
