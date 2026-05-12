<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Institute;
use App\Models\LlmCallLog;
use App\Models\Payment;
use App\Models\Question;
use App\Models\Test;
use App\Models\TestTemplate;
use App\Models\TemplateQuestion;
use App\Models\User;
use App\Models\UserEvent;
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
        // is_admin is intentionally NOT mass-assignable on User — use forceFill
        // inside this admin-gated path. AdminMiddleware already enforces the
        // caller is itself an admin.
        $user->forceFill(['is_admin' => !$user->is_admin])->save();
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

    // ─── Feedback ─────────────────────────────────────────────────────────────

    public function feedbackIndex(Request $request)
    {
        $query = Feedback::with('user')->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        $feedbacks = $query->paginate(25)->withQueryString();
        $counts = [
            'new'       => Feedback::where('status', 'new')->count(),
            'reviewing' => Feedback::where('status', 'reviewing')->count(),
            'resolved'  => Feedback::where('status', 'resolved')->count(),
            'total'     => Feedback::count(),
        ];

        return view('admin.feedback.index', compact('feedbacks', 'counts'));
    }

    public function feedbackStatus(Request $request, Feedback $feedback)
    {
        $request->validate(['status' => 'required|in:new,reviewing,resolved,dismissed']);
        $feedback->update(['status' => $request->status]);
        return back()->with('success', 'Feedback updated.');
    }

    // ─── LLM Usage / Cost ─────────────────────────────────────────────────────

    public function llmUsage(Request $request)
    {
        $days = (int) $request->get('days', 14);
        $days = max(1, min(90, $days));
        $since = now()->subDays($days);

        $totals = [
            'calls_total'    => LlmCallLog::where('created_at', '>=', $since)->count(),
            'calls_ok'       => LlmCallLog::where('created_at', '>=', $since)->where('ok', true)->count(),
            'tokens_in'      => (int) LlmCallLog::where('created_at', '>=', $since)->sum('input_tokens'),
            'tokens_out'     => (int) LlmCallLog::where('created_at', '>=', $since)->sum('output_tokens'),
            'cost_usd'       => (float) LlmCallLog::where('created_at', '>=', $since)->sum('cost_usd'),
            'avg_latency_ms' => (int) LlmCallLog::where('created_at', '>=', $since)->where('ok', true)->avg('latency_ms'),
        ];

        $byProvider = LlmCallLog::where('created_at', '>=', $since)
            ->select('provider', DB::raw('count(*) as calls'), DB::raw('sum(input_tokens) as tin'), DB::raw('sum(output_tokens) as tout'), DB::raw('sum(cost_usd) as cost'))
            ->groupBy('provider')
            ->orderByDesc('cost')
            ->get();

        $byModel = LlmCallLog::where('created_at', '>=', $since)
            ->select('provider', 'model', DB::raw('count(*) as calls'), DB::raw('sum(cost_usd) as cost'))
            ->groupBy('provider', 'model')
            ->orderByDesc('cost')
            ->get();

        $byDay = LlmCallLog::where('created_at', '>=', $since)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as calls'), DB::raw('sum(cost_usd) as cost'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topUsers = LlmCallLog::where('created_at', '>=', $since)
            ->whereNotNull('user_id')
            ->select('user_id', DB::raw('count(*) as calls'), DB::raw('sum(cost_usd) as cost'))
            ->groupBy('user_id')
            ->orderByDesc('cost')
            ->limit(10)
            ->with('user:id,name,email')
            ->get();

        $recent = LlmCallLog::with('user:id,name,email')
            ->latest('created_at')
            ->limit(20)
            ->get();

        return view('admin.llm-usage.index', compact('days', 'totals', 'byProvider', 'byModel', 'byDay', 'topUsers', 'recent'));
    }

    // ─── Analytics ────────────────────────────────────────────────────────────

    public function analytics(Request $request)
    {
        $days = (int) $request->get('days', 14);
        $days = max(1, min(90, $days));
        $since = now()->subDays($days);

        $eventCounts = class_exists(UserEvent::class)
            ? UserEvent::where('created_at', '>=', $since)
                ->select('event', DB::raw('count(*) as c'))
                ->groupBy('event')
                ->orderByDesc('c')
                ->pluck('c', 'event')
            : collect();

        $signupsBySource = User::where('created_at', '>=', $since)
            ->select(DB::raw("COALESCE(NULLIF(ref_source,''),'direct') as source"), DB::raw('count(*) as c'))
            ->groupBy('source')
            ->orderByDesc('c')
            ->pluck('c', 'source');

        $testsByType = Test::where('created_at', '>=', $since)
            ->select('type', DB::raw('count(*) as c'))
            ->groupBy('type')
            ->pluck('c', 'type');

        $funnel = [
            'signups'         => User::where('created_at', '>=', $since)->count(),
            'started_test'    => Test::where('created_at', '>=', $since)->distinct('user_id')->count('user_id'),
            'completed_test'  => Test::where('created_at', '>=', $since)->where('status', 'completed')->distinct('user_id')->count('user_id'),
            'submitted_fb'    => Feedback::where('created_at', '>=', $since)->whereNotNull('user_id')->distinct('user_id')->count('user_id'),
        ];

        return view('admin.analytics.index', compact('days', 'eventCounts', 'signupsBySource', 'testsByType', 'funnel'));
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
