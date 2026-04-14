<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-surface-700 hover:bg-surface-600 border border-surface-600 rounded-xl font-semibold text-sm text-surface-100 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-surface-500 focus:ring-offset-2 focus:ring-offset-surface-950 disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
