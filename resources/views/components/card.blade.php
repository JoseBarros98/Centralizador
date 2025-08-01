<div {{ $attributes->merge(['class' => 'bg-white overflow-hidden shadow-sm sm:rounded-lg']) }}>
    @if(isset($header))
        <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
            {{ $header }}
        </div>
    @endif
    
    <div class="p-4 sm:p-6 {{ isset($header) ? '' : 'border-b border-gray-200' }}">
        {{ $slot }}
    </div>
    
    @if(isset($footer))
        <div class="px-4 py-4 sm:px-6 bg-gray-50 border-t border-gray-200">
            {{ $footer }}
        </div>
    @endif
</div>
