@props(['value' => null, 'for'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-700']) }} for="{{ $for }}">
    {{ $value ?? $slot }}
</label>
