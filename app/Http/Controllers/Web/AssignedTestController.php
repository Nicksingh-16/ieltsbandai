<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\AssignmentNotificationMail;
use App\Models\AssignedTest;
use App\Models\AssignedTestStudent;
use App\Models\Batch;
use App\Models\Question;
use App\Models\Test;
use App\Models\TestQuestion;
use App\Models\TestTemplate;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AssignedTestController extends Controller
{
    // ─── Teacher: List assignments for the institute ──────────────────────────

    public function index()
    {
        $institute   = $this->getInstituteOrFail();
        $assignments = AssignedTest::where('institute_id', $institute->id)
            ->with(['template', 'batch', 'assigner'])
            ->withCount(['studentRecords', 'studentRecords as completed_count' => fn($q) => $q->where('status', 'completed')])
            ->latest()
            ->paginate(20);

        return view('pages.institute.assignments.index', compact('institute', 'assignments'));
    }

    // ─── Teacher: Create assignment form ─────────────────────────────────────

    public function create()
    {
        $this->authorizeTeacher();
        $institute = $this->getInstituteOrFail();

        $templates = TestTemplate::where('institute_id', $institute->id)
            ->where('is_active', true)
            ->get();

        $batches = $institute->batches()->where('is_active', true)->get();

        return view('pages.institute.assignments.create', compact('institute', 'templates', 'batches'));
    }

    // ─── Teacher: Store new assignment ───────────────────────────────────────

    public function store(Request $request)
    {
        $this->authorizeTeacher();
        $institute = $this->getInstituteOrFail();

        $data = $request->validate([
            'test_template_id' => 'required|exists:test_templates,id',
            'batch_id'         => 'required|exists:batches,id',
            'title'            => 'required|string|max:255',
            'instructions'     => 'nullable|string|max:2000',
            'due_date'         => 'nullable|date|after:now',
            'is_mandatory'     => 'boolean',
            'allows_retake'    => 'boolean',
        ]);

        // Ensure template belongs to this institute
        $template = TestTemplate::where('id', $data['test_template_id'])
            ->where('institute_id', $institute->id)
            ->firstOrFail();

        // Ensure batch belongs to this institute
        $batch = Batch::where('id', $data['batch_id'])
            ->where('institute_id', $institute->id)
            ->firstOrFail();

        DB::transaction(function () use ($data, $institute, $template, $batch) {
            $assignment = AssignedTest::create([
                'test_template_id' => $template->id,
                'institute_id'     => $institute->id,
                'batch_id'         => $batch->id,
                'assigned_by'      => Auth::id(),
                'title'            => $data['title'],
                'instructions'     => $data['instructions'] ?? null,
                'due_date'         => $data['due_date'] ?? null,
                'is_mandatory'     => $data['is_mandatory'] ?? true,
                'allows_retake'    => $data['allows_retake'] ?? false,
                'status'           => 'active',
            ]);

            // Enroll all current batch students
            $batch->load('students');
            $assignment->enrollBatch($batch);

            // Notify each student via email (queued)
            $assignment->load('institute');
            foreach ($batch->students as $student) {
                Mail::to($student->email)->queue(
                    new AssignmentNotificationMail($student, $assignment)
                );
            }
        });

        return redirect()->route('institute.assignments.index')
            ->with('success', "Test assigned to {$batch->name}.");
    }

    // ─── Teacher: View assignment detail + per-student status ─────────────────

    public function show(AssignedTest $assignment)
    {
        $institute = $this->getInstituteOrFail();
        abort_if($assignment->institute_id !== $institute->id, 403);

        $assignment->load(['template', 'batch', 'assigner']);
        $records = $assignment->studentRecords()
            ->with(['student', 'test'])
            ->get();

        return view('pages.institute.assignments.show', compact('institute', 'assignment', 'records'));
    }

    // ─── Teacher: Close/re-open an assignment ────────────────────────────────

    public function toggleStatus(AssignedTest $assignment)
    {
        $this->authorizeTeacher();
        $institute = $this->getInstituteOrFail();
        abort_if($assignment->institute_id !== $institute->id, 403);

        $assignment->update([
            'status' => $assignment->status === 'active' ? 'closed' : 'active',
        ]);

        return back()->with('success', 'Assignment status updated.');
    }

    // ─── Student: My assigned tests ──────────────────────────────────────────

    public function myTests()
    {
        $user    = Auth::user();
        $records = AssignedTestStudent::where('user_id', $user->id)
            ->with(['assignment.template', 'assignment.batch', 'test'])
            ->whereHas('assignment', fn($q) => $q->where('status', 'active'))
            ->orderByRaw("FIELD(status, 'pending', 'started', 'completed', 'skipped')")
            ->get();

        return view('pages.institute.assignments.my-tests', compact('records'));
    }

    // ─── Student: Start an assigned test ─────────────────────────────────────

    public function startAssigned(AssignedTest $assignment)
    {
        $user   = Auth::user();
        $record = AssignedTestStudent::where('assigned_test_id', $assignment->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        abort_if($assignment->status !== 'active', 403, 'This assignment is closed.');
        abort_if(
            $record->status === 'completed' && !$assignment->allows_retake,
            403,
            'You have already completed this test.'
        );

        $template = $assignment->template()->with('questions')->firstOrFail();

        abort_if($template->questions->isEmpty(), 422, 'This question set has no questions yet.');

        // Route to the correct module based on template type
        return match($template->type) {
            'writing'   => $this->startWritingFromTemplate($template, $record, $assignment),
            'speaking'  => redirect()->route('speaking.test')
                            ->with('assignment_id', $assignment->id),
            'listening' => redirect()->route('listening.index')
                            ->with('assignment_id', $assignment->id),
            'reading'   => redirect()->route('reading.index')
                            ->with('assignment_id', $assignment->id),
            default     => back()->with('error', 'Full mock assigned tests coming soon.'),
        };
    }

    // ─── Private: Start a writing test from a template ───────────────────────

    private function startWritingFromTemplate(TestTemplate $template, AssignedTestStudent $record, AssignedTest $assignment): \Illuminate\Http\RedirectResponse
    {
        // Pick first question from template (or random if multiple writing questions exist)
        $question = $template->questions->shuffle()->first();

        $test = DB::transaction(function () use ($question, $template, $assignment, $record) {
            $testType = str_contains($question->category, 'academic') ? 'academic' : 'general';

            $test = Test::create([
                'user_id'    => Auth::id(),
                'type'       => 'writing',
                'category'   => $question->category,
                'test_type'  => $testType,
                'status'     => 'in_progress',
                'started_at' => now(),
                'metadata'   => json_encode([
                    'from_assignment' => $assignment->id,
                    'template_id'     => $template->id,
                    'time_limit'      => $template->duration_minutes * 60,
                ]),
            ]);

            TestQuestion::create([
                'test_id'     => $test->id,
                'question_id' => $question->id,
                'part'        => str_contains($question->category, 'task2') ? 2 : 1,
            ]);

            // Mark student record as started
            $record->update([
                'status'     => 'started',
                'started_at' => now(),
                'test_id'    => $test->id,
            ]);

            return $test;
        });

        // Deduct credit
        app(CreditService::class)->deductCredit(Auth::user());

        return redirect()->route('writing.test', $test->id);
    }

    // ─── Called after a test completes — marks assignment record done ─────────
    // (Wired from WritingTestService::scoreAndComplete after job finishes)

    public static function markCompleted(int $testId): void
    {
        $record = AssignedTestStudent::where('test_id', $testId)->first();
        if ($record && $record->status !== 'completed') {
            $record->update([
                'status'       => 'completed',
                'completed_at' => now(),
            ]);
        }
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function getInstituteOrFail()
    {
        $institute = Auth::user()->institute;
        abort_if(!$institute, 403, 'You do not belong to any institute.');
        return $institute;
    }

    private function authorizeTeacher(): void
    {
        abort_unless(Auth::user()->isTeacher(), 403);
    }
}
