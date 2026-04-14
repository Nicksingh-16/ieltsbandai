@props(['disabled' => false])

<input
    @disabled($disabled)
    {{ $attributes->merge(['class' => 'w-full bg-surface-900 border border-surface-600 text-surface-100 placeholder-surface-400 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed']) }}
>
