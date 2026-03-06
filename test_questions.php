<?php

use App\Repositories\SpeakingRepository;
use App\Models\Question;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $repo = new SpeakingRepository();
    $questions = $repo->getSpeakingQuestions();
    echo "Success: Questions found.\n";
    print_r($questions);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
