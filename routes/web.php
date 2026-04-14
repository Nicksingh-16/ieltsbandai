<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\AssignedTestController;
use App\Http\Controllers\Web\InstituteController;
use App\Http\Controllers\Web\MockTestController;
use App\Http\Controllers\Web\StudyPlanController;
use App\Http\Controllers\Web\ReferralController;
use App\Http\Controllers\Web\WritingTestController;
use App\Http\Controllers\Web\ListeningTestController;
use App\Http\Controllers\Web\ReadingTestController;
use App\Http\Controllers\Web\ContactController;
use App\Http\Controllers\Web\DemoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\web\PricingController;
use App\Http\Controllers\web\PaymentController;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::view('/pricing', 'pages.pricing')->name('pricing');
Route::view('/about', 'pages.about')->name('about');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');
Route::view('/faq', 'pages.faq')->name('faq');
Route::view('/privacy', 'pages.privacy')->name('privacy');
Route::view('/terms', 'pages.terms')->name('terms');

// Demo — no signup required
Route::get('/demo', [DemoController::class, 'index'])->name('demo');
Route::post('/demo/submit', [DemoController::class, 'submit'])->name('demo.submit');
Route::get('/demo/result', [DemoController::class, 'result'])->name('demo.result');

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
Route::middleware(['auth', 'verified'])->prefix('speaking')->name('speaking.')->group(function () {
    Route::get('/', function () {
        return view('pages.speaking.landing');
    })->name('index');

    Route::get('/test', [\App\Http\Controllers\Web\SpeakingTestController::class, 'show'])
        ->middleware('check.credits')
        ->name('test');

    Route::post('/upload/audio', [\App\Http\Controllers\Web\SpeakingTestController::class, 'uploadAudio'])
        ->name('upload.audio');
});

// Writing Test Routes
Route::middleware(['auth', 'verified'])->prefix('writing')->name('writing.')->group(function () {
    Route::get('/', [WritingTestController::class, 'index'])->name('index');

    Route::post('/start', [WritingTestController::class, 'start'])
        ->middleware('check.credits')
        ->name('start');

    Route::get('/test/{testId}', [WritingTestController::class, 'showTest'])->name('test');
    Route::post('/submit/{testId}', [WritingTestController::class, 'submit'])->name('submit');
    Route::post('/draft/{testId}', [WritingTestController::class, 'saveDraft'])->name('draft');
    Route::get('/result/{testId}', [WritingTestController::class, 'result'])->name('result');
    Route::get('/result/{testId}/band9', [WritingTestController::class, 'band9Rewrite'])->name('band9');
    Route::get('/result/{testId}/pdf', [WritingTestController::class, 'downloadPdf'])->name('pdf');

    Route::post('/analyze-vocabulary', [WritingTestController::class, 'analyzeVocabulary'])
        ->middleware('throttle:20,1')
        ->name('analyze.vocabulary');

    Route::post('/result/{testId}/clarify', [WritingTestController::class, 'clarify'])
        ->middleware('throttle:30,1')
        ->name('clarify');
});

// Listening Test Routes
Route::middleware(['auth', 'verified'])->prefix('listening')->name('listening.')->group(function () {
    Route::get('/', [ListeningTestController::class, 'index'])->name('index');
    Route::post('/start', [ListeningTestController::class, 'start'])->middleware('check.credits')->name('start');
    Route::post('/submit/{testId}', [ListeningTestController::class, 'submit'])->name('submit');
    Route::get('/result/{testId}', [ListeningTestController::class, 'result'])->name('result');
});

// Reading Test Routes
Route::middleware(['auth', 'verified'])->prefix('reading')->name('reading.')->group(function () {
    Route::get('/', [ReadingTestController::class, 'index'])->name('index');
    Route::post('/start', [ReadingTestController::class, 'start'])->middleware('check.credits')->name('start');
    Route::post('/submit/{testId}', [ReadingTestController::class, 'submit'])->name('submit');
    Route::get('/result/{testId}', [ReadingTestController::class, 'result'])->name('result');
});

// Common Test Result Route (for both speaking and writing)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/test/{test}/result', [\App\Http\Controllers\Web\TestResultController::class, 'show'])
        ->name('test.result');

    // Lightweight status polling endpoint — used by result page JS
    Route::get('/api/test/{testId}/status', function ($testId) {
        $test = \App\Models\Test::where('id', $testId)
            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->firstOrFail();

        $audioFiles      = $test->audioFiles;
        $transcribedCount = $audioFiles->whereNotNull('transcript')->where('transcript', '!=', '')->count();

        return response()->json([
            'status'      => $test->status,
            'band'        => $test->overall_band,
            'transcribed' => $transcribedCount,
            'total_audio' => $audioFiles->count(),
        ]);
    })->name('test.status');
});

// ─── Full Mock Test Routes ────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->prefix('mock-test')->name('mock-test.')->group(function () {
    Route::get('/', [MockTestController::class, 'index'])->name('index');
    Route::post('/start', [MockTestController::class, 'start'])->middleware('check.credits')->name('start');
    Route::get('/{mock}/module/{module}', [MockTestController::class, 'module'])->name('module');
    Route::post('/{mock}/advance/{module}', [MockTestController::class, 'advance'])->name('advance');
    Route::get('/{mock}/result', [MockTestController::class, 'result'])->name('result');
    Route::get('/history', [MockTestController::class, 'history'])->name('history');
    Route::post('/{mock}/abandon', [MockTestController::class, 'abandon'])->name('abandon');
});

// ─── Study Plan ───────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/study-plan/{test}', [StudyPlanController::class, 'show'])->name('study-plan.show');
    Route::post('/study-plan/{test}/regenerate', [StudyPlanController::class, 'regenerate'])->name('study-plan.regenerate');
});

// ─── Referral System ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->prefix('referral')->name('referral.')->group(function () {
    Route::get('/', [ReferralController::class, 'show'])->name('show');
});
Route::get('/ref/{code}', [ReferralController::class, 'track'])->name('referral.track');

// ─── SEO Public Tools ─────────────────────────────────────────────────────────
Route::get('/ielts-band-calculator', function () {
    return view('pages.tools.band-calculator');
})->name('tools.band-calculator');

Route::get('/ielts-writing-checker', function () {
    return view('pages.tools.writing-checker');
})->name('tools.writing-checker');

Route::get('/ielts-writing-samples', fn() => view('pages.tools.writing-samples'))->name('tools.writing-samples');
Route::get('/ielts-speaking-topics', fn() => view('pages.tools.speaking-topics'))->name('tools.speaking-topics');
Route::get('/ielts-vocabulary-list', fn() => view('pages.tools.vocabulary-list'))->name('tools.vocabulary-list');

Route::post('/ielts-writing-checker/analyze', [\App\Http\Controllers\Web\WritingCheckerController::class, 'analyze'])
    ->middleware('throttle:10,1')
    ->name('tools.writing-checker.analyze');

// ─── Admin Routes ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Users
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{user}', [AdminController::class, 'userShow'])->name('users.show');
    Route::post('/users/{user}/credits', [AdminController::class, 'userAddCredits'])->name('users.credits');
    Route::post('/users/{user}/suspend', [AdminController::class, 'userSuspend'])->name('users.suspend');
    Route::post('/users/{user}/toggle-admin', [AdminController::class, 'userMakeAdmin'])->name('users.toggle-admin');

    // Questions (global / B2C bank)
    Route::get('/questions', [AdminController::class, 'questions'])->name('questions');
    Route::get('/questions/create', [AdminController::class, 'questionCreate'])->name('questions.create');
    Route::post('/questions', [AdminController::class, 'questionStore'])->name('questions.store');
    Route::get('/questions/{question}/edit', [AdminController::class, 'questionEdit'])->name('questions.edit');
    Route::put('/questions/{question}', [AdminController::class, 'questionUpdate'])->name('questions.update');
    Route::delete('/questions/{question}', [AdminController::class, 'questionDestroy'])->name('questions.destroy');

    // Question Sets (global / B2C)
    Route::get('/question-sets', [AdminController::class, 'questionSets'])->name('question-sets.index');
    Route::get('/question-sets/create', [AdminController::class, 'questionSetCreate'])->name('question-sets.create');
    Route::post('/question-sets', [AdminController::class, 'questionSetStore'])->name('question-sets.store');
    Route::get('/question-sets/{set}', [AdminController::class, 'questionSetShow'])->name('question-sets.show');
    Route::post('/question-sets/{set}/questions', [AdminController::class, 'questionSetAddQuestion'])->name('question-sets.add-question');
    Route::delete('/question-sets/{set}/questions/{question}', [AdminController::class, 'questionSetRemoveQuestion'])->name('question-sets.remove-question');
    Route::delete('/question-sets/{set}', [AdminController::class, 'questionSetDestroy'])->name('question-sets.destroy');

    // Payments
    Route::get('/payments', [AdminController::class, 'payments'])->name('payments');

    // Institutes
    Route::get('/institutes', [AdminController::class, 'institutes'])->name('institutes');
    Route::get('/institutes/{institute}', [AdminController::class, 'instituteShow'])->name('institutes.show');
    Route::post('/institutes/{institute}/toggle', [AdminController::class, 'instituteToggle'])->name('institutes.toggle');
    Route::post('/institutes/{institute}/plan', [AdminController::class, 'instituteUpdatePlan'])->name('institutes.plan');
});

// ─── Institute (B2B) Routes ───────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->prefix('institute')->name('institute.')->group(function () {
    Route::get('/', [InstituteController::class, 'landing'])->name('landing');
    Route::post('/register', [InstituteController::class, 'register'])->name('register');
    Route::get('/dashboard', [InstituteController::class, 'dashboard'])->name('dashboard');

    // Batches
    Route::post('/batch', [InstituteController::class, 'batchCreate'])->name('batch.create');
    Route::get('/batch/{batch}', [InstituteController::class, 'batchShow'])->name('batch.show');
    Route::get('/batch/{batch}/analytics', [InstituteController::class, 'batchAnalytics'])->name('batch.analytics');
    Route::post('/batch/{batch}/invite', [InstituteController::class, 'inviteStudent'])->name('batch.invite');
    Route::post('/batch/{batch}/import', [InstituteController::class, 'bulkImport'])->name('batch.import');

    // Question Bank (institute-private questions)
    Route::get('/questions', [InstituteController::class, 'questionBank'])->name('questions.index');
    Route::get('/questions/create', [InstituteController::class, 'questionCreate'])->name('questions.create');
    Route::post('/questions', [InstituteController::class, 'questionStore'])->name('questions.store');
    Route::get('/questions/{question}/edit', [InstituteController::class, 'questionEdit'])->name('questions.edit');
    Route::put('/questions/{question}', [InstituteController::class, 'questionUpdate'])->name('questions.update');
    Route::delete('/questions/{question}', [InstituteController::class, 'questionDestroy'])->name('questions.destroy');

    // Assignments (teacher assigns question sets to batches)
    Route::get('/assignments', [AssignedTestController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/create', [AssignedTestController::class, 'create'])->name('assignments.create');
    Route::post('/assignments', [AssignedTestController::class, 'store'])->name('assignments.store');
    Route::get('/assignments/{assignment}', [AssignedTestController::class, 'show'])->name('assignments.show');
    Route::post('/assignments/{assignment}/toggle', [AssignedTestController::class, 'toggleStatus'])->name('assignments.toggle');

    // Student: my assigned tests + start
    Route::get('/my-tests', [AssignedTestController::class, 'myTests'])->name('my-tests');
    Route::post('/assigned/{assignment}/start', [AssignedTestController::class, 'startAssigned'])->name('assigned.start');

    // Question Sets (institute-private)
    Route::get('/question-sets', [InstituteController::class, 'questionSets'])->name('question-sets.index');
    Route::get('/question-sets/create', [InstituteController::class, 'questionSetCreate'])->name('question-sets.create');
    Route::post('/question-sets', [InstituteController::class, 'questionSetStore'])->name('question-sets.store');
    Route::get('/question-sets/{set}', [InstituteController::class, 'questionSetShow'])->name('question-sets.show');
    Route::post('/question-sets/{set}/questions', [InstituteController::class, 'questionSetAddQuestion'])->name('question-sets.add-question');
    Route::delete('/question-sets/{set}/questions/{question}', [InstituteController::class, 'questionSetRemoveQuestion'])->name('question-sets.remove-question');
    Route::delete('/question-sets/{set}', [InstituteController::class, 'questionSetDestroy'])->name('question-sets.destroy');
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

// ── Institute B2B Payment Routes ──────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/institute/pricing', [PaymentController::class, 'institutePricing'])->name('institute.pricing');
    Route::post('/payment/institute/initiate', [PaymentController::class, 'initiateInstitute'])->name('payment.institute.initiate');
    Route::get('/payment/institute/success', [PaymentController::class, 'successInstitute'])->name('payment.institute.success');
});

