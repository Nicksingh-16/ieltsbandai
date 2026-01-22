{{-- resources/views/auth/register.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - IELTS Band AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 min-h-screen">

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4 py-8">
        <!-- Logo -->
        <div class="mb-8">
            <a href="{{ route('home') }}" class="flex flex-col items-center">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 w-16 h-16 rounded-2xl flex items-center justify-center mb-3 shadow-lg">
                    <i class="fas fa-microphone text-white text-2xl"></i>
                </div>
                <span class="text-2xl font-bold text-indigo-600">IELTS Band AI</span>
            </a>
        </div>

        <!-- Register Card -->
        <div class="w-full sm:max-w-md bg-white rounded-3xl shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-6">
                <h2 class="text-2xl font-bold text-white text-center">Create Your Account</h2>
                <p class="text-indigo-100 text-center mt-1">Start your free IELTS practice today</p>
            </div>

            <!-- Form -->
            <div class="px-8 py-8">
                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <!-- Name -->
                    <div class="mb-5">
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                            Full Name
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input 
                                id="name" 
                                type="text" 
                                name="name" 
                                value="{{ old('name') }}" 
                                required 
                                autofocus 
                                autocomplete="name"
                                class="block w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition-all @error('name') border-red-500 @enderror"
                                placeholder="John Doe"
                            >
                        </div>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle text-xs"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Email Address -->
                    <div class="mb-5">
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                            Email Address
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input 
                                id="email" 
                                type="email" 
                                name="email" 
                                value="{{ old('email') }}" 
                                required 
                                autocomplete="username"
                                class="block w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition-all @error('email') border-red-500 @enderror"
                                placeholder="your@email.com"
                            >
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle text-xs"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-5">
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input 
                                id="password" 
                                type="password" 
                                name="password" 
                                required 
                                autocomplete="new-password"
                                class="block w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition-all @error('password') border-red-500 @enderror"
                                placeholder="Create a strong password"
                            >
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle text-xs"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                            Confirm Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input 
                                id="password_confirmation" 
                                type="password" 
                                name="password_confirmation" 
                                required 
                                autocomplete="new-password"
                                class="block w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition-all @error('password_confirmation') border-red-500 @enderror"
                                placeholder="Re-enter your password"
                            >
                        </div>
                        @error('password_confirmation')
                            <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle text-xs"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Terms & Privacy -->
                    <div class="mb-6">
                        <p class="text-xs text-gray-600 text-center">
                            By signing up, you agree to our 
                            <a href="#" class="text-indigo-600 hover:text-indigo-700 font-medium">Terms of Service</a> 
                            and 
                            <a href="#" class="text-indigo-600 hover:text-indigo-700 font-medium">Privacy Policy</a>
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3 px-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98]"
                    >
                        <span class="flex items-center justify-center gap-2">
                            <i class="fas fa-user-plus"></i>
                            Create Account
                        </span>
                    </button>
                </form>

                <!-- Divider -->
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white text-gray-500">Or sign up with</span>
                    </div>
                </div>

                <!-- Social Sign Up -->
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <a href="{{ route('auth.google') }}" class="flex items-center justify-center gap-2 bg-white border-2 border-gray-300 hover:border-indigo-400 text-gray-700 font-medium py-3 px-4 rounded-xl transition-all hover:bg-gray-50 hover:shadow-md">
                        <i class="fab fa-google text-red-500"></i>
                        Google
                    </a>
                    <button type="button" disabled class="flex items-center justify-center gap-2 bg-gray-100 border-2 border-gray-200 text-gray-400 font-medium py-3 px-4 rounded-xl cursor-not-allowed">
                        <i class="fab fa-facebook text-gray-400"></i>
                        Coming Soon
                    </button>
                </div>

                <!-- Login Link -->
                <div class="text-center">
                    <p class="text-gray-600">
                        Already have an account? 
                        <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-700 font-semibold">
                            Log in
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Benefits Section -->
        <div class="mt-8 max-w-md">
            <div class="bg-white/50 backdrop-blur rounded-2xl p-6 border border-white/20">
                <h3 class="text-center font-bold text-gray-900 mb-4">Why Join IELTS Band AI?</h3>
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-check text-indigo-600 text-sm"></i>
                        </div>
                        <p class="text-sm text-gray-700">Free daily practice tests</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-check text-indigo-600 text-sm"></i>
                        </div>
                        <p class="text-sm text-gray-700">Instant AI band score feedback</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-check text-indigo-600 text-sm"></i>
                        </div>
                        <p class="text-sm text-gray-700">Detailed error corrections</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Links -->
        <div class="mt-6 text-center text-sm text-gray-600">
            <a href="{{ route('home') }}" class="hover:text-indigo-600 mx-3">Home</a>
            <span class="text-gray-400">•</span>
            <a href="#" class="hover:text-indigo-600 mx-3">Privacy</a>
            <span class="text-gray-400">•</span>
            <a href="#" class="hover:text-indigo-600 mx-3">Terms</a>
        </div>
    </div>

</body>
</html>