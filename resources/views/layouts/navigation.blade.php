{{-- Dark Electric Navigation --}}
<nav x-data="{ open: false }" class="bg-surface-900 border-b border-surface-600/60 sticky top-0 z-50 backdrop-blur-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">

            {{-- ── Logo ── --}}
            <div class="flex items-center gap-6 lg:gap-8">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 shrink-0">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-400 to-brand-700 flex items-center justify-center shadow-glow">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                            <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                        </svg>
                    </div>
                    <span class="text-lg font-bold text-surface-50 hidden sm:block">
                        IELTS Band <span class="text-gradient">AI</span>
                    </span>
                </a>

                {{-- ── Desktop Nav Links ── --}}
                <div class="hidden md:flex items-center gap-1">
                    <a href="{{ route('dashboard') }}"
                       class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </a>

                    {{-- ── Tests Dropdown ── --}}
                    @php
                    $testRoutes = ['writing.*','speaking.*','listening.*','reading.*','mock-test.*'];
                    $testActive = collect($testRoutes)->contains(fn($r) => request()->routeIs($r));
                    @endphp
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button @click="open = !open"
                            class="nav-link {{ $testActive ? 'nav-link-active' : '' }} cursor-pointer select-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Tests
                            <svg class="w-3 h-3 text-surface-500 transition-transform duration-150" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-1"
                             class="absolute left-0 top-full mt-2 w-56 bg-surface-800 border border-surface-600 rounded-2xl shadow-card-hover py-1.5 z-50"
                             style="display:none;">

                            {{-- Mock Test — featured at top --}}
                            <a href="{{ route('mock-test.index') }}" @click="open = false"
                               class="flex items-center gap-3 px-4 py-2.5 text-sm {{ request()->routeIs('mock-test.*') ? 'text-brand-400 bg-brand-500/10' : 'text-surface-300 hover:text-surface-50 hover:bg-surface-700' }} transition-colors rounded-xl mx-1.5 mb-1">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                                <span class="flex-1 font-semibold">Full Mock Test</span>
                                <span class="tag-cyan text-[9px] px-1.5 py-0.5">New</span>
                            </a>

                            <div class="h-px bg-surface-700 mx-4 mb-1"></div>

                            <a href="{{ route('writing.index') }}" @click="open = false"
                               class="flex items-center gap-3 px-4 py-2.5 text-sm {{ request()->routeIs('writing.*') ? 'text-brand-400 bg-brand-500/10' : 'text-surface-300 hover:text-surface-50 hover:bg-surface-700' }} transition-colors rounded-xl mx-1.5">
                                <svg class="w-4 h-4 shrink-0 text-purple-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                </svg>
                                Writing
                                <span class="text-surface-500 text-xs ml-auto">60 min</span>
                            </a>

                            <a href="{{ route('speaking.index') }}" @click="open = false"
                               class="flex items-center gap-3 px-4 py-2.5 text-sm {{ request()->routeIs('speaking.*') ? 'text-brand-400 bg-brand-500/10' : 'text-surface-300 hover:text-surface-50 hover:bg-surface-700' }} transition-colors rounded-xl mx-1.5">
                                <svg class="w-4 h-4 shrink-0 text-brand-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                                    <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                                </svg>
                                Speaking
                                <span class="text-surface-500 text-xs ml-auto">14 min</span>
                            </a>

                            <a href="{{ route('listening.index') }}" @click="open = false"
                               class="flex items-center gap-3 px-4 py-2.5 text-sm {{ request()->routeIs('listening.*') ? 'text-brand-400 bg-brand-500/10' : 'text-surface-300 hover:text-surface-50 hover:bg-surface-700' }} transition-colors rounded-xl mx-1.5">
                                <svg class="w-4 h-4 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M12 6a7 7 0 010 12M8.464 8.464a5 5 0 000 7.072"/>
                                </svg>
                                Listening
                                <span class="text-surface-500 text-xs ml-auto">40 min</span>
                            </a>

                            <a href="{{ route('reading.index') }}" @click="open = false"
                               class="flex items-center gap-3 px-4 py-2.5 text-sm {{ request()->routeIs('reading.*') ? 'text-brand-400 bg-brand-500/10' : 'text-surface-300 hover:text-surface-50 hover:bg-surface-700' }} transition-colors rounded-xl mx-1.5">
                                <svg class="w-4 h-4 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                Reading
                                <span class="text-surface-500 text-xs ml-auto">60 min</span>
                            </a>
                        </div>
                    </div>

                    @if(auth()->user()->institute_id && auth()->user()->isTeacher())
                    <a href="{{ route('institute.dashboard') }}"
                       class="nav-link {{ request()->routeIs('institute.*') ? 'nav-link-active' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Institute
                    </a>
                    @endif

                    @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}"
                       class="nav-link {{ request()->routeIs('admin.*') ? 'nav-link-active' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Admin
                    </a>
                    @endif
                </div>
            </div>

            {{-- ── Right Side ── --}}
            <div class="hidden md:flex items-center gap-3">
                {{-- Upgrade button (with credit count badge for free users) --}}
                @if(!auth()->user()->hasActiveSubscription())
                <a href="{{ route('pricing') }}"
                   class="relative inline-flex items-center gap-1.5 bg-gradient-to-r from-brand-500 to-brand-600 hover:from-brand-400 hover:to-brand-500 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow-glow transition-all duration-150">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    Upgrade
                    <span class="absolute -top-2 -right-2 min-w-[18px] h-[18px] bg-surface-900 border border-brand-400 text-brand-300 text-[10px] font-bold rounded-full flex items-center justify-center px-1 leading-none">
                        {{ auth()->user()->test_credits ?? 0 }}
                    </span>
                </a>
                @else
                <span class="tag-cyan">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    Pro
                </span>
                @endif

                {{-- User Dropdown --}}
                <div class="relative" x-data="{ dropdownOpen: false }" @mouseenter="dropdownOpen = true" @mouseleave="dropdownOpen = false">
                    <button @click="dropdownOpen = !dropdownOpen"
                        class="flex items-center gap-2 px-2 py-1.5 rounded-xl bg-surface-800 border border-surface-600 hover:border-surface-500 transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white font-bold text-xs">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <span class="hidden lg:block text-sm font-medium text-surface-200 max-w-[120px] truncate">{{ Auth::user()->name }}</span>
                        <svg class="w-3.5 h-3.5 text-surface-400 transition-transform duration-150" :class="{ 'rotate-180': dropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Dropdown --}}
                    <div x-show="dropdownOpen"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         class="absolute right-0 mt-2 w-60 bg-surface-800 border border-surface-600 rounded-2xl shadow-card-hover py-1.5 z-50"
                         style="display:none;">

                        {{-- User info --}}
                        <div class="px-4 py-3 border-b border-surface-600">
                            <p class="text-sm font-semibold text-surface-50 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-surface-400 truncate mt-0.5">{{ Auth::user()->email }}</p>
                            @if(auth()->user()->hasActiveSubscription())
                                <span class="tag-cyan mt-2">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    Pro Member
                                </span>
                            @else
                                <span class="tag bg-surface-700 text-surface-400 border border-surface-600 mt-2">Free — {{ auth()->user()->test_credits ?? 0 }} credits left</span>
                            @endif
                        </div>

                        {{-- Menu items --}}
                        <div class="py-1">
                            <a href="{{ route('dashboard') }}" @click="dropdownOpen = false" class="flex items-center gap-3 px-4 py-2.5 text-sm text-surface-300 hover:text-surface-50 hover:bg-surface-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                Dashboard
                            </a>
                            <a href="{{ route('profile.edit') }}" @click="dropdownOpen = false" class="flex items-center gap-3 px-4 py-2.5 text-sm text-surface-300 hover:text-surface-50 hover:bg-surface-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Profile Settings
                            </a>
                            @if(!auth()->user()->hasActiveSubscription())
                            <a href="{{ route('pricing') }}" @click="dropdownOpen = false" class="flex items-center gap-3 px-4 py-2.5 text-sm text-brand-400 hover:text-brand-300 hover:bg-surface-700 transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                Upgrade to Pro
                            </a>
                            @endif
                        </div>

                        {{-- Logout --}}
                        <div class="border-t border-surface-600 pt-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Mobile Hamburger ── --}}
            <button @click="open = !open"
                class="md:hidden p-2 rounded-lg text-surface-400 hover:text-surface-200 hover:bg-surface-800 transition-colors focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path :class="{ 'hidden': open }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    <path :class="{ 'hidden': !open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- ── Mobile Menu ── --}}
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden md:hidden border-t border-surface-600 bg-surface-900">
        {{-- Nav links --}}
        <div class="px-4 py-3 space-y-1">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-surface-700 text-brand-400' : 'text-surface-300 hover:bg-surface-800 hover:text-surface-100' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="{{ route('mock-test.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('mock-test.*') ? 'bg-surface-700 text-brand-400' : 'text-surface-300 hover:bg-surface-800 hover:text-surface-100' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Full Mock Test
                <span class="tag-cyan text-[10px]">New</span>
            </a>
            <a href="{{ route('writing.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('writing.*') ? 'bg-surface-700 text-brand-400' : 'text-surface-300 hover:bg-surface-800 hover:text-surface-100' }}">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                Writing Test
            </a>
            <a href="{{ route('speaking.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('speaking.*') ? 'bg-surface-700 text-brand-400' : 'text-surface-300 hover:bg-surface-800 hover:text-surface-100' }}">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/><path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
                Speaking Test
            </a>
            <a href="{{ route('listening.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('listening.*') ? 'bg-surface-700 text-brand-400' : 'text-surface-300 hover:bg-surface-800 hover:text-surface-100' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M12 6a7 7 0 010 12M8.464 8.464a5 5 0 000 7.072"/></svg>
                Listening Test
                <span class="tag-cyan text-[10px]">New</span>
            </a>
            <a href="{{ route('reading.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('reading.*') ? 'bg-surface-700 text-brand-400' : 'text-surface-300 hover:bg-surface-800 hover:text-surface-100' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Reading Test
                <span class="tag-cyan text-[10px]">New</span>
            </a>
        </div>

        {{-- User section --}}
        <div class="px-4 py-4 border-t border-surface-600">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white font-bold text-sm">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-surface-50 truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-surface-400 truncate">{{ Auth::user()->email }}</p>
                </div>
            </div>

            @if(!auth()->user()->hasActiveSubscription())
            <a href="{{ route('pricing') }}" class="btn-primary w-full mb-2 text-sm">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                Upgrade to Pro
            </a>
            @endif

            <div class="flex gap-2">
                <a href="{{ route('profile.edit') }}" class="btn-secondary flex-1 text-sm">Profile</a>
                <form method="POST" action="{{ route('logout') }}" class="flex-1">
                    @csrf
                    <button type="submit" class="btn-danger w-full text-sm">Log Out</button>
                </form>
            </div>
        </div>
    </div>
</nav>
