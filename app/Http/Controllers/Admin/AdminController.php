<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institute;
use App\Models\Payment;
use App\Models\Question;
use App\Models\Test;
use App\Models\TestTemplate;
use App\Models\TemplateQuestion;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // ─── Dashboard ────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $stats = [
            'total_users'      => User::count(),
            'active_users'     => User::where('created_at', '>=', now()->subDays(30))->count(),
            'pro_users'        => User::where('is_pro', true)->count(),
            'total_tests'      => Test::count(),
            'tests_today'      => Test::whereDate('created_at', today())->count(),
            'total_revenue'    => Payment::where('status', 'completed')->sum('amount'),
            'revenue_month'    => Payment::where('status', 'completed')
                                    ->whereMonth('created_at', now()->month)
                                    ->sum('amount'),
            'total_institutes' => Institute::count(),
            'total_questions'  => Question::count(),
        ];

        // Daily registrations last 14 days
        $registrations = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        // Daily revenue last 14 days
        $dailyRevenue = Payment::select(DB::raw('DATE(created_at) as date'), DB::raw('sum(amount) as total'))
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        return view('admin.dashboard', compact('stats', 'registrations', 'dailyRevenue'));
    }

    // ─── Users ────────────────────────────────────────────────────────────────

    public function users(Request $request)
    {
        $query = User::with('subscription')->latest();

        if ($search = $request->get('search')) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        if ($filter = $request->get('filter')) {
            match ($filter) {
                'pro'   => $query->where('is_pro', true),
                'free'  => $query->where('is_pro', false),
                'admin' => $query->where('is_admin', true),
                default => null,
            };
        }

        $users = $query->paginate(25)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function userShow(User $user)
    {
        $user->load('tests', 'subscription', 'payments', 'institute');
        $recentTests = $user->tests()->latest()->limit(10)->get();
        return view('admin.users.show', compact('user', 'recentTests'));
    }

    public function userAddCredits(Request $request, User $user)
    {
        $request->validate(['credits' => 'required|integer|min:1|max:500']);
        app(CreditService::class)->addCredits($user, $request->credits);
        return back()->with('success', "Added {$request->credits} credits to {$user->name}.");
    }

    public function userSuspend(User $user)
    {
        $user->update(['email_verified_at' => null]);
        return back()->with('success', "User {$user->name} suspended.");
    }

    public function userMakeAdmin(User $user)
    {
        $user->update(['is_admin' => !$user->is_admin]);
        $action = $user->is_admin ? 'granted' : 'revoked';
        return back()->with('success', "Admin access {$action} for {$user->name}.");
    }

    // ─── Questions ────────────────────────────────────────────────────────────

    public function questions(Request $request)
    {
        $query = Question::latest();

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($difficulty = $request->get('difficulty')) {
            $query->whereJsonContains('metadata->difficulty', $difficulty);
        }

        $questions  = $query->paginate(30)->withQueryString();
        $types      = Question::distinct()->pluck('type');

        return view('admin.questions.index', compact('questions', 'types'));
    }

    public function questionCreate()
    {
        return view('admin.questions.create');
    }

    public function questionStore(Request $request)
    {
        $data = $request->validate([
            'type'         => 'required|in:speaking,writing,listening,reading',
            'category'     => 'required|string',
            'title'        => 'required|string|max:500',
            'content'      => 'required|string',
            'media_url'    => 'nullable|url|max:500',
            'time_limit'   => 'nullable|integer|min:1',
            'min_words'    => 'nullable|integer|min:1',
            'difficulty'   => 'nullable|in:easy,medium,hard',
        ]);

        $metadata = [];
        if (!empty($data['difficulty'])) {
            $metadata['difficulty'] = $data['difficulty'];
        }
        unset($data['difficulty']);

        Question::create($data + ['metadata' => $metadata ?: null]);

        return redirect()->route('admin.questions')->with('success', 'Question created.');
    }

    public function questionEdit(Question $question)
    {
        return view('admin.questions.edit', compact('question'));
    }

    public function questionUpdate(Request $request, Question $question)
    {
        $data = $request->validate([
            'type'         => 'required|in:speaking,writing,listening,reading',
            'category'     => 'required|string',
            'title'        => 'required|string|max:500',
            'content'      => 'required|string',
            'media_url'    => 'nullable|url|max:500',
            'time_limit'   => 'nullable|integer|min:1',
            'min_words'    => 'nullable|integer|min:1',
            'difficulty'   => 'nullable|in:easy,medium,hard',
            'active'       => 'boolean',
        ]);

        $metadata = $question->metadata ?? [];
        if (!empty($data['difficulty'])) {
            $metadata['difficulty'] = $data['difficulty'];
        }
        unset($data['difficulty']);

        $question->update($data + ['metadata' => $metadata]);

        return redirect()->route('admin.questions')->with('success', 'Question updated.');
    }

    public function questionDestroy(Question $question)
    {
        $question->delete();
        return back()->with('success', 'Question deleted.');
    }

    // ─── Question Sets (Master / Global) ──────────────────────────────────────

    public function questionSets(Request $request)
    {
        $query = TestTemplate::whereNull('institute_id')->withCount('questions')->latest();

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $sets = $query->paginate(20)->withQueryString();
        return view('admin.question-sets.index', compact('sets'));
    }

    public function questionSetCreate()
    {
        return view('admin.question-sets.create');
    }

    public function questionSetStore(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string|max:1000',
            'type'             => 'required|in:writing,speaking,listening,reading,full_mock',
            'duration_minutes' => 'required|integer|min:1|max:480',
            'is_public'        => 'boolean',
            'is_active'        => 'boolean',
        ]);

        $set = TestTemplate::create($data + [
            'institute_id' => null,
            'created_by'   => auth()->id(),
            'is_public'    => $request->boolean('is_public', true),
            'is_active'    => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.question-sets.show', $set)
            ->with('success', 'Question set created. Now add questions to it.');
    }

    public function questionSetShow(TestTemplate $set)
    {
        abort_if($set->institute_id !== null, 404);
        $set->load('questions');

        $availableQuestions = Question::whereNull('institute_id')
            ->where('type', $set->type !== 'full_mock' ? $set->type : null, $set->type === 'full_mock')
            ->whereNotIn('id', $set->questions->pluck('id'))
            ->latest()
            ->get();

        return view('admin.question-sets.show', compact('set', 'availableQuestions'));
    }

    public function questionSetAddQuestion(Request $request, TestTemplate $set)
    {
        abort_if($set->institute_id !== null, 404);
        $request->validate(['question_id' => 'required|exists:questions,id']);

        $maxOrder = $set->questions()->max('template_questions.order') ?? 0;

        $set->questions()->syncWithoutDetaching([
            $request->question_id => ['order' => $maxOrder + 1],
        ]);

        return back()->with('success', 'Question added to set.');
    }

    public function questionSetRemoveQuestion(TestTemplate $set, Question $question)
    {
        abort_if($set->institute_id !== null, 404);
        $set->questions()->detach($question->id);
        return back()->with('success', 'Question removed.');
    }

    public function questionSetDestroy(TestTemplate $set)
    {
        abort_if($set->institute_id !== null, 404);
        $set->delete();
        return redirect()->route('admin.question-sets.index')->with('success', 'Question set deleted.');
    }

    // ─── Payments ─────────────────────────────────────────────────────────────

    public function payments(Request $request)
    {
        $query = Payment::with('user')->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $payments      = $query->paginate(25)->withQueryString();
        $totalRevenue  = Payment::where('status', 'completed')->sum('amount');

        return view('admin.payments.index', compact('payments', 'totalRevenue'));
    }

    // ─── Institutes ───────────────────────────────────────────────────────────

    public function institutes()
    {
        $institutes = Institute::with('owner')->withCount('members')->latest()->paginate(20);
        return view('admin.institutes.index', compact('institutes'));
    }

    public function instituteShow(Institute $institute)
    {
        $institute->load('owner', 'batches');
        $members      = $institute->members()->with('tests')->get();
        $questionSets = TestTemplate::where('institute_id', $institute->id)->withCount('questions')->get();
        $totalTests   = $members->sum(fn($m) => $m->tests->count());
        return view('admin.institutes.show', compact('institute', 'members', 'questionSets', 'totalTests'));
    }

    public function instituteToggle(Institute $institute)
    {
        $institute->update(['is_active' => !$institute->is_active]);
        $status = $institute->is_active ? 'activated' : 'suspended';
        return back()->with('success', "Institute {$status}.");
    }

    public function instituteUpdatePlan(Request $request, Institute $institute)
    {
        $request->validate([
            'plan'       => 'required|in:free,basic,pro,enterprise',
            'seat_limit' => 'required|integer|min:1|max:10000',
        ]);

        $institute->update([
            'plan'       => $request->plan,
            'seat_limit' => $request->seat_limit,
        ]);

        return back()->with('success', 'Plan updated.');
    }
}
