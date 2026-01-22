<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IELTS Band AI - Free Speaking & Writing Mock Tests</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-white">

<!-- Header -->
<header class="bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center">
                <span class="text-xl sm:text-2xl font-bold text-indigo-600">IELTS Band AI</span>
            </div>
            <nav class="flex items-center space-x-2 sm:space-x-4">
                <a href="{{ route('login') }}" class="text-sm sm:text-base text-gray-700 hover:text-indigo-600 font-medium px-2 sm:px-3 py-2">Login</a>
                <a href="{{ route('register') }}" class="text-sm sm:text-base bg-indigo-600 text-white px-3 sm:px-4 py-2 rounded-lg font-medium hover:bg-indigo-700">Sign Up</a>
                <a href="{{ route('pricing') }}" class="hidden sm:block text-sm sm:text-base text-indigo-600 border border-indigo-600 px-3 sm:px-4 py-2 rounded-lg font-medium hover:bg-indigo-50">Upgrade</a>
            </nav>
        </div>
    </div>
</header>

<!-- Hero Section -->
<section class="bg-gradient-to-b from-indigo-50 to-white py-12 sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-center gap-8 lg:gap-12">
            <div class="flex-1 text-center lg:text-left">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight mb-4 sm:mb-6">Free IELTS Speaking & Writing AI Mock Tests</h1>
                <p class="text-lg sm:text-xl text-gray-600 mb-6 sm:mb-8">Instant Band Score • Error Highlighting • Mobile Friendly</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="{{ route('speaking.test') }}" class="bg-indigo-600 text-white px-8 py-4 rounded-xl text-lg font-semibold hover:bg-indigo-700 shadow-lg hover:shadow-xl transition-all inline-block text-center">
                        <i class="fas fa-microphone mr-2"></i>Start Speaking Test
                    </a>
                    <a href="{{ route('writing.index') }}" class="bg-white text-indigo-600 border-2 border-indigo-600 px-8 py-4 rounded-xl text-lg font-semibold hover:bg-indigo-50 shadow-lg hover:shadow-xl transition-all inline-block text-center">
                        <i class="fas fa-pen mr-2"></i>Start Writing Test
                    </a>
                </div>
            </div>
            <div class="flex-1 hidden lg:flex justify-center">
                <div class="relative w-full max-w-md">
                    <div class="bg-indigo-100 rounded-3xl p-8 flex items-center justify-center h-80">
                        <div class="text-center">
                            <i class="fas fa-microphone text-indigo-600 text-8xl mb-4"></i>
                            <i class="fas fa-pen text-indigo-400 text-6xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Social Proof -->
<div class="bg-indigo-600 py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="text-white text-base sm:text-lg font-medium">
            <span class="text-yellow-300">⭐⭐⭐⭐⭐</span> 1000+ Indian students practice daily
        </p>
    </div>
</div>

<!-- Features Section -->
<section class="py-12 sm:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">
            <div class="bg-gray-50 rounded-2xl p-6 sm:p-8 text-center hover:shadow-lg transition-shadow">
                <div class="bg-indigo-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-bolt text-indigo-600 text-2xl"></i>
                </div>
                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-3">Instant AI Band Score</h3>
                <p class="text-gray-600 text-base">Get your IELTS band score immediately after submitting your test</p>
            </div>
            <div class="bg-gray-50 rounded-2xl p-6 sm:p-8 text-center hover:shadow-lg transition-shadow">
                <div class="bg-indigo-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-microphone-alt text-indigo-600 text-2xl"></i>
                </div>
                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-3">Speaking Recorder</h3>
                <p class="text-gray-600 text-base">Record your answers using browser microphone with real-time feedback</p>
            </div>
            <div class="bg-gray-50 rounded-2xl p-6 sm:p-8 text-center hover:shadow-lg transition-shadow">
                <div class="bg-indigo-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-highlighter text-indigo-600 text-2xl"></i>
                </div>
                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-3">Error Highlighting</h3>
                <p class="text-gray-600 text-base">See your mistakes highlighted with detailed corrections and explanations</p>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-12 sm:py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl sm:text-4xl font-bold text-center text-gray-900 mb-12">How It Works</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="bg-indigo-600 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">1</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Start Test</h3>
                <p class="text-gray-600">Choose Speaking or Writing test and begin your practice</p>
            </div>
            <div class="text-center">
                <div class="bg-indigo-600 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">2</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Submit</h3>
                <p class="text-gray-600">Complete your test and submit for AI evaluation</p>
            </div>
            <div class="text-center">
                <div class="bg-indigo-600 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">3</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Get Band Score</h3>
                <p class="text-gray-600">Receive instant band score with detailed feedback</p>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Banner -->
<section class="py-12 sm:py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-3xl p-8 sm:p-12 text-white">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                <div>
                    <h2 class="text-3xl sm:text-4xl font-bold mb-4">Upgrade to Pro</h2>
                    <div class="space-y-3 mb-6">
                        <p class="text-lg"><i class="fas fa-check-circle mr-2"></i>Unlimited tests per day</p>
                        <p class="text-lg"><i class="fas fa-check-circle mr-2"></i>Full correction details</p>
                        <p class="text-lg"><i class="fas fa-check-circle mr-2"></i>PDF reports & analytics</p>
                    </div>
                    <a href="{{ route('pricing') }}" class="bg-white text-indigo-600 px-8 py-4 rounded-xl text-lg font-bold hover:bg-gray-100 shadow-lg inline-block">
                        Upgrade Now - ₹99/month
                    </a>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-2xl p-6 text-center">
                    <p class="text-sm uppercase tracking-wide mb-2">Free Plan</p>
                    <p class="text-5xl font-bold mb-2">₹0</p>
                    <p class="text-lg mb-4">1 test per day</p>
                    <p class="text-sm opacity-80">Basic feedback included</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="bg-gray-900 text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-8">
            <div>
                <h4 class="font-bold mb-4">Company</h4>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="{{ route('about') }}" class="hover:text-white">About</a></li>
                    <li><a href="{{ route('contact') }}" class="hover:text-white">Contact</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold mb-4">Support</h4>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="{{ route('faq') }}" class="hover:text-white">FAQ</a></li>
                    <li><a href="{{ route('privacy') }}" class="hover:text-white">Privacy</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold mb-4">Social</h4>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="#" class="hover:text-white"><i class="fab fa-instagram mr-2"></i>Instagram</a></li>
                    <li><a href="#" class="hover:text-white"><i class="fab fa-youtube mr-2"></i>YouTube</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold mb-4">IELTS Band AI</h4>
                <p class="text-gray-400 text-sm">Practice IELTS with AI-powered feedback</p>
            </div>
        </div>
        <div class="border-t border-gray-800 pt-8 text-center text-gray-400 text-sm">
            <p>&copy; {{ date('Y') }} IELTS Band AI. All rights reserved.</p>
        </div>
    </div>
</footer>

</body>
</html>