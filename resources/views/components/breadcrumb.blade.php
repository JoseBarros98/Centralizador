@props(['links' => []])

@if(count($links) > 0)
<nav class="flex items-center space-x-1 mb-4 text-sm" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-indigo-600 transition-colors flex items-center">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
    </a>
    @foreach($links as $label => $url)
        <svg class="h-4 w-4 text-slate-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        @if($loop->last)
            <span class="font-medium text-slate-700 truncate max-w-xs">{{ $label }}</span>
        @else
            <a href="{{ $url }}" class="text-slate-400 hover:text-indigo-600 transition-colors truncate max-w-xs">{{ $label }}</a>
        @endif
    @endforeach
</nav>
@endif
