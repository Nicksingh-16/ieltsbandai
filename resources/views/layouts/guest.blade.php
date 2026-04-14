<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'IELTS Band AI') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-surface-950 text-surface-200">

    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12 relative">

        <!-- Background glow -->
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-brand-500/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-1/4 w-80 h-80 bg-brand-700/10 rounded-full blur-3xl"></div>
        </div>

        <!-- Logo -->
        <div class="mb-8 text-center relative z-10">
            <a href="/" class="inline-flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-brand-400 to-brand-700 flex items-center justify-center shadow-glow">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                        <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                    </svg>
                </div>
                <span class="text-xl font-bold text-surface-50">IELTS Band <span class="text-gradient">AI</span></span>
            </a>
        </div>

        <!-- Card -->
        <div class="relative z-10 w-full max-w-md bg-surface-800 border border-surface-600 rounded-2xl shadow-card p-8">
            {{ $slot }}
        </div>

        <p class="mt-6 text-sm text-surface-500 relative z-10">
            &copy; {{ date('Y') }} IELTS Band AI. All rights reserved.
        </p>
    </div>

</body>
</html>
