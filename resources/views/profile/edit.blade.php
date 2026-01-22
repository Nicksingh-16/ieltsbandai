<x-app-layout>
    <div class="w-full min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-indigo-600">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <span class="text-lg sm:text-xl font-bold text-gray-900">Profile & Settings</span>
                    <div class="w-8"></div>
                </div>
            </div>
        </header>

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Profile Card -->
            <div class="bg-white rounded-2xl shadow-md p-6 sm:p-8 mb-6">
                <div class="flex items-center gap-6 mb-6">
                    <div class="w-20 h-20 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-full flex items-center justify-center text-white text-3xl font-bold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ Auth::user()->name }}</h2>
                        <p class="text-gray-600">{{ Auth::user()->email }}</p>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-xl p-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Subscription Status</p>
                        <p class="text-lg font-bold text-gray-900">
                            @if(Auth::user()->hasActiveSubscription())
                                Pro Plan
                            @else
                                Free Plan
                            @endif
                        </p>
                    </div>
                    @if(!Auth::user()->hasActiveSubscription())
                    <a href="{{ route('pricing') }}" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-indigo-700">
                        Upgrade
                    </a>
                    @endif
                </div>
            </div>

            <!-- Account Settings -->
            <div class="bg-white rounded-2xl shadow-md mb-6 overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900">Account Settings</h3>
                </div>
                <div class="p-6 sm:p-8">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- Change Password -->
            <div class="bg-white rounded-2xl shadow-md mb-6 overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900">Change Password</h3>
                </div>
                <div class="p-6 sm:p-8">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900">Delete Account</h3>
                </div>
                <div class="p-6 sm:p-8">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>