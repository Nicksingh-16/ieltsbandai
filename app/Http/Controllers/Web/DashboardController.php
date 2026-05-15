<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AssignedTestStudent;
use App\Models\MockTest;
use App\Models\Test;
use App\Repositories\TestRepository;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(protected TestRepository $testRepository) {}

    public function index()
    {
        $userId = Auth::id();
        $tests = $this->testRepository->getUserTestHistory($userId, paginate: true);

        // Mock test children should be excluded from per-module charts, latest
        // bands and the standalone test list — the parent MockTest record is
        // what the dashboard surfaces. Same exclusion logic as TestRepository.
        $mockChildIds = MockTest::where('user_id', $userId)
            ->get(['listening_test_id', 'reading_test_id', 'writing_test_id', 'speaking_test_id'])
            ->flatMap(fn ($m) => [
                $m->listening_test_id, $m->reading_test_id,
                $m->writing_test_id,   $m->speaking_test_id,
            ])
            ->filter()->unique()->values();

        $moduleTypes = [
            'listening' => ['listening_academic', 'listening_general'],
            'reading' => ['reading_academic', 'reading_general'],
            'writing' => ['writing_academic', 'writing_general'],
            'speaking' => ['speaking'],
        ];

        // Last 10 completed tests per module for progress charts
        $chartData = [];
        foreach ($moduleTypes as $label => $types) {
            $rows = Test::where('user_id', $userId)
                ->whereIn('type', $types)
                ->whereNotNull('overall_band')
                ->where('status', 'completed')
                ->when($mockChildIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $mockChildIds))
                ->orderBy('created_at')
                ->limit(10)
                ->get(['id', 'overall_band', 'created_at']);

            $chartData[$label] = [
                'labels' => $rows->map(fn ($t) => $t->created_at->format('d M'))->values()->toArray(),
                'scores' => $rows->map(fn ($t) => (float) $t->overall_band)->values()->toArray(),
            ];
        }

        // Latest band per module + improvement delta (current vs previous)
        $latestBands = [];
        $improvementDeltas = [];
        foreach ($moduleTypes as $label => $types) {
            $last2 = Test::where('user_id', $userId)
                ->whereIn('type', $types)
                ->whereNotNull('overall_band')
                ->where('status', 'completed')
                ->when($mockChildIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $mockChildIds))
                ->latest()
                ->limit(2)
                ->pluck('overall_band');

            if ($last2->isNotEmpty()) {
                $latestBands[$label] = (float) $last2->first();
                $improvementDeltas[$label] = $last2->count() > 1
                    ? round((float) $last2->first() - (float) $last2->last(), 1)
                    : null;
            }
        }

        // Recent mock tests — one row per mock, ranked latest first. Surfaced
        // on the dashboard as the single source of truth for full-mock history.
        $recentMockTests = MockTest::where('user_id', $userId)
            ->whereIn('status', ['completed', 'in_progress'])
            ->latest()
            ->limit(5)
            ->get();

        $overallBand = count($latestBands)
            ? round(array_sum($latestBands) / count($latestBands) * 2) / 2
            : null;

        // Per-criteria scores for most recent writing + speaking tests
        $criteriaBreakdown = [];
        foreach (['writing', 'speaking'] as $mod) {
            $latestTest = Test::where('user_id', $userId)
                ->whereIn('type', $moduleTypes[$mod])
                ->where('status', 'completed')
                ->when($mockChildIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $mockChildIds))
                ->latest()
                ->with('testScores')
                ->first();

            if ($latestTest && $latestTest->testScores->isNotEmpty()) {
                $criteriaBreakdown[$mod] = $latestTest->testScores
                    ->pluck('band_score', 'criteria')
                    ->toArray();
            }
        }

        // Pending assignments for institute students
        $pendingAssignments = Auth::user()->institute_id
            ? AssignedTestStudent::where('user_id', $userId)
                ->whereIn('status', ['pending', 'started'])
                ->whereHas('assignment', fn ($q) => $q->where('status', 'active'))
                ->with('assignment.template')
                ->get()
            : collect();

        return view('pages.dashboard.index', compact(
            'tests', 'chartData', 'latestBands', 'overallBand',
            'improvementDeltas', 'criteriaBreakdown', 'pendingAssignments',
            'recentMockTests'
        ));
    }
}
