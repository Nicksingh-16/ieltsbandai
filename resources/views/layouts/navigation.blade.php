{{-- resources/views/layouts/navigation.blade.php --}}
<nav x-data="{ open: false }" class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 w-10 h-10 rounded-xl flex items-center justify-center">
                            <i class="fas fa-microphone text-white text-lg"></i>
                        </div>
                        <span class="text-xl font-bold text-indigo-600 hidden sm:block">IELTS Band AI</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-2 sm:-my-px sm:ms-8 lg:ms-10 sm:flex">
                    <a href="{{ route('dashboard') }}" 
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50' }}">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    
                    <a href="{{ route('speaking.test') }}" 
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('speaking.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50' }}">
                        <i class="fas fa-microphone"></i>
                        <span>Speaking</span>
                    </a>
                    
                    <a href="{{ route('writing.index') }}" 
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-all {{ request()->routeIs('writing.*') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-600 hover:text-indigo-600 hover:bg-gray-50' }}">
                        <i class="fas fa-pen"></i>
                        <span>Writing</span>
                    </a>
                </div>
            </div>

            <!-- Right Side Navigation -->
            <div class="hidden sm:flex sm:items-center sm:gap-3">
                <!-- Upgrade Button -->
                @if(!auth()->user()->hasActiveSubscription())
                <a href="{{ route('pricing') }}" 
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-white px-4 py-2 rounded-lg font-medium text-sm shadow-md hover:shadow-lg transition-all transform hover:scale-105">
                    <i class="fas fa-crown"></i>
                    <span>Upgrade</span>
                </a>
                @endif

                <!-- User Dropdown -->
                <div class="relative" x-data="{ dropdownOpen: false }">
                    <button @click="dropdownOpen = !dropdownOpen" 
                        class="inline-flex items-center gap-2 px-3 py-2 border border-gray-200 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all">
                        <div class="w-8 h-8 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <span class="hidden lg:block">{{ Auth::user()->name }}</span>
                        <svg class="w-4 h-4 transition-transform" :class="{'rotate-180': dropdownOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="dropdownOpen" 
                        @click.away="dropdownOpen = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50"
                        style="display: none;">
                        
                        <!-- User Info -->
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                            @if(auth()->user()->hasActiveSubscription())
                                <span class="inline-flex items-center gap-1 mt-2 bg-yellow-50 text-yellow-700 text-xs font-medium px-2 py-1 rounded-full">
                                    <i class="fas fa-crown text-xs"></i>
                                    Pro Member
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 mt-2 bg-gray-100 text-gray-600 text-xs font-medium px-2 py-1 rounded-full">
                                    Free Plan
                                </span>
                            @endif
                        </div>

                        <!-- Menu Items -->
                        <div class="py-1">
                            <a href="{{ route('dashboard') }}" 
                                class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                <i class="fas fa-home w-4"></i>
                                <span>Dashboard</span>
                            </a>
                            
                            <a href="{{ route('profile.edit') }}" 
                                class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                <i class="fas fa-user w-4"></i>
                                <span>Profile Settings</span>
                            </a>

                            @if(!auth()->user()->hasActiveSubscription())
                            <a href="{{ route('pricing') }}" 
                                class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                <i class="fas fa-crown w-4"></i>
                                <span>Upgrade to Pro</span>
                            </a>
                            @else
                            <a href="{{ route('subscription.manage') }}" 
                                class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                <i class="fas fa-credit-card w-4"></i>
                                <span>Manage Subscription</span>
                            </a>
                            @endif

                            <a href="#" 
                                class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                <i class="fas fa-question-circle w-4"></i>
                                <span>Help & Support</span>
                            </a>
                        </div>

                        <!-- Logout -->
                        <div class="border-t border-gray-100 py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" 
                                    class="flex items-center gap-3 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fas fa-sign-out-alt w-4"></i>
                                    <span>Log Out</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Hamburger -->
            <div class="flex items-center sm:hidden">
                <button @click="open = !open" 
                    class="inline-flex items-center justify-center p-2 rounded-lg text-gray-600 hover:text-indigo-600 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition-all">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Menu -->
    <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden border-t border-gray-200">
        <!-- Navigation Links -->
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('dashboard') }}" 
                class="flex items-center gap-3 px-4 py-3 text-base font-medium {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-600 border-l-4 border-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600' }}">
                <i class="fas fa-home w-5"></i>
                <span>Dashboard</span>
            </a>
            
            <a href="{{ route('speaking.test') }}" 
                class="flex items-center gap-3 px-4 py-3 text-base font-medium {{ request()->routeIs('speaking.*') ? 'bg-indigo-50 text-indigo-600 border-l-4 border-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600' }}">
                <i class="fas fa-microphone w-5"></i>
                <span>Speaking Test</span>
            </a>
            
            <a href="{{ route('writing.index') }}" 
                class="flex items-center gap-3 px-4 py-3 text-base font-medium {{ request()->routeIs('writing.*') ? 'bg-indigo-50 text-indigo-600 border-l-4 border-indigo-600' : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600' }}">
                <i class="fas fa-pen w-5"></i>
                <span>Writing Test</span>
            </a>
        </div>

        <!-- User Section -->
        <div class="pt-4 pb-3 border-t border-gray-200">
            <div class="px-4 mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">{{ Auth::user()->name }}</div>
                        <div class="text-sm text-gray-500">{{ Auth::user()->email }}</div>
                    </div>
                </div>
                @if(auth()->user()->hasActiveSubscription())
                    <span class="inline-flex items-center gap-1 mt-2 bg-yellow-50 text-yellow-700 text-xs font-medium px-2 py-1 rounded-full">
                        <i class="fas fa-crown text-xs"></i>
                        Pro Member
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 mt-2 bg-gray-100 text-gray-600 text-xs font-medium px-2 py-1 rounded-full">
                        Free Plan
                    </span>
                @endif
            </div>

            <div class="space-y-1">
                @if(!auth()->user()->hasActiveSubscription())
                <a href="{{ route('pricing') }}" 
                    class="flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-yellow-400 to-yellow-500 text-white font-medium">
                    <i class="fas fa-crown w-5"></i>
                    <span>Upgrade to Pro</span>
                </a>
                @endif

                <a href="{{ route('profile.edit') }}" 
                    class="flex items-center gap-3 px-4 py-3 text-base font-medium text-gray-600 hover:bg-gray-50 hover:text-indigo-600">
                    <i class="fas fa-user w-5"></i>
                    <span>Profile Settings</span>
                </a>

                <a href="#" 
                    class="flex items-center gap-3 px-4 py-3 text-base font-medium text-gray-600 hover:bg-gray-50 hover:text-indigo-600">
                    <i class="fas fa-question-circle w-5"></i>
                    <span>Help & Support</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                        class="flex items-center gap-3 w-full px-4 py-3 text-base font-medium text-red-600 hover:bg-red-50">
                        <i class="fas fa-sign-out-alt w-5"></i>
                        <span>Log Out</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>