@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-surface-300 mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>
