@props(['title', 'value', 'icon', 'color' => 'blue', 'change' => null, 'changeType' => null])

@php
$colorClasses = [
    'blue' => 'bg-blue-500',
    'green' => 'bg-green-500',
    'red' => 'bg-red-500',
    'yellow' => 'bg-yellow-500',
    'indigo' => 'bg-indigo-500',
    'purple' => 'bg-purple-500',
    'pink' => 'bg-pink-500',
    'gray' => 'bg-gray-500',
    'primary' => 'bg-blue-600',
    'secondary' => 'bg-gray-600',
    'success' => 'bg-green-600',
    'danger' => 'bg-red-600',
    'warning' => 'bg-yellow-500',
    'info' => 'bg-blue-400',
    'light' => 'bg-gray-200',
    'dark' => 'bg-gray-800',
    'orange' => 'bg-orange-500',
    'teal' => 'bg-teal-500',
    'cyan' => 'bg-cyan-500',
][$color] ?? 'bg-blue-500';

$changeColor = $changeType === 'increase' ? 'text-green-500' : ($changeType === 'decrease' ? 'text-red-500' : 'text-gray-500');
@endphp

<div class="bg-white overflow-hidden shadow rounded-lg">
    <div class="p-5">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="{{ $colorClasses }} rounded-md p-3">
                    {!! $icon !!}
                </div>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                        {{ $title }}
                    </dt>
                    <dd>
                        <div class="text-lg font-medium text-gray-900">
                            {{ $value }}
                        </div>
                    </dd>
                    @if($change)
                    <dd class="flex items-center text-sm {{ $changeColor }}">
                        @if($changeType === 'increase')
                        <svg class="flex-shrink-0 self-center h-5 w-5 {{ $changeColor }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        @elseif($changeType === 'decrease')
                        <svg class="flex-shrink-0 self-center h-5 w-5 {{ $changeColor }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        @endif
                        <span class="ml-1">{{ $change }}</span>
                    </dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</div>
