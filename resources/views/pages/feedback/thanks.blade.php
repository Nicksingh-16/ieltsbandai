@php($pageTitle = 'Thanks for your feedback')
<x-app-layout>
<div class="min-h-[80vh] flex items-center justify-center px-4 py-12">
    <div class="max-w-md text-center">
        <div class="w-16 h-16 mx-auto rounded-full bg-emerald-500/15 grid place-items-center mb-6">
            <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-2xl md:text-3xl font-bold text-white mb-3">Thanks — we got it.</h1>
        <p class="text-surface-400 mb-8">Every piece of beta feedback helps us tune the model and the product. We read every message and reply when needed.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('dashboard') }}" class="btn-primary">Back to dashboard</a>
            <a href="{{ route('feedback.create') }}" class="btn-secondary">Send another</a>
        </div>
    </div>
</div>
</x-app-layout>
