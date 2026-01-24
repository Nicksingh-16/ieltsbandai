<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\WritingTestController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\web\PricingController;
use App\Http\Controllers\web\PaymentController;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::view('/pricing', 'pages.pricing')->name('pricing');
Route::view('/about', 'pages.about')->name('about');
Route::view('/contact', 'pages.contact')->name('contact');
Route::view('/faq', 'pages.faq')->name('faq');
Route::view('/privacy', 'pages.privacy')->name('privacy');

// Social Authentication Routes
Route::get('auth/google', [\App\Http\Controllers\Auth\SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [\App\Http\Controllers\Auth\SocialAuthController::class, 'handleGoogleCallback']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Web\DashboardController::class, 'index'])
        ->name('dashboard');
});

// Speaking Test Routes
Route::middleware(['auth'])->prefix('speaking')->name('speaking.')->group(function () {
    Route::get('/test', [\App\Http\Controllers\Web\SpeakingTestController::class, 'show'])
        ->middleware('check.credits')
        ->name('test');
    
    Route::post('/upload/audio', [\App\Http\Controllers\Web\SpeakingTestController::class, 'uploadAudio'])
        ->name('upload.audio');
});

// Writing Test Routes
Route::middleware(['auth'])->prefix('writing')->name('writing.')->group(function () {

    // Test selection page (index)
    Route::get('/', [WritingTestController::class, 'index'])->name('index'); // writing.index

    // Start writing test (POST)
    Route::post('/start', [WritingTestController::class, 'start'])
        ->middleware('check.credits')
        ->name('start'); // writing.start

    // Submit essay
    Route::post('/submit/{testId}', [WritingTestController::class, 'submit'])->name('submit'); // writing.submit

    // Save draft
    Route::post('/draft/{testId}', [WritingTestController::class, 'saveDraft'])->name('draft'); // writing.draft

    // Show result
    Route::get('/result/{testId}', [WritingTestController::class, 'result'])->name('result'); // writing.result
    
    // Analyze vocabulary (real-time)
    Route::post('/analyze-vocabulary', [WritingTestController::class, 'analyzeVocabulary'])->name('analyze.vocabulary'); // writing.analyze.vocabulary
});


// Common Test Result Route (for both speaking and writing)
Route::middleware(['auth'])->group(function () {
    Route::get('/test/{test}/result', [\App\Http\Controllers\Web\TestResultController::class, 'show'])
        ->name('test.result');
});

require __DIR__.'/auth.php';


// Pricing page
// Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');

// Payment routes
Route::post('/payment/initiate', [PaymentController::class, 'initiate'])
    ->name('payment.initiate')
    ->middleware('auth');

Route::get('/payment/success', [PaymentController::class, 'success'])
    ->name('payment.success')
    ->middleware('auth');

Route::post('/payment/webhook', [PaymentController::class, 'webhook'])
    ->name('payment.webhook');

// Database Connection Test Route
Route::get('/db-test', function () {
    try {
        $count = \DB::table('users')->count();
        return response()->json([
            'status' => 'success',
            'message' => 'Database connection successful',
            'user_count' => $count,
            'database' => config('database.default'),
            'host' => config('database.connections.pgsql.host'), // showing configured host
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ], 500);
    }
});

// Temporary Route to Run Migrations
Route::get('/run-migrations', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return response()->json([
            'status' => 'success',
            'message' => 'Migrations executed successfully',
            'output' => \Illuminate\Support\Facades\Artisan::output(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});