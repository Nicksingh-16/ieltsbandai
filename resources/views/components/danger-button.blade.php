<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 active:bg-red-800 border border-transparent rounded-xl font-semibold text-sm text-white transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-surface-950']) }}>
    {{ $slot }}
</button>
