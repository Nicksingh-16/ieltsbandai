<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Institute;
use App\Models\Question;
use App\Models\TestTemplate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InstituteController extends Controller
{
    // ─── Landing / Registration ───────────────────────────────────────────────

    public function landing()
    {
        if (Auth::user()->institute_id) {
            return redirect()->route('institute.dashboard');
        }
        return view('pages.institute.landing');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'contact_email'=> 'required|email',
            'phone'        => 'nullable|string|max:20',
            'city'         => 'nullable|string|max:100',
            'gst_number'   => 'nullable|string|max:20',
        ]);

        $user = Auth::user();

        if ($user->institute_id) {
            return back()->with('error', 'You already belong to an institute.');
        }

        $institute = Institute::create([
            'name'          => $request->name,
            'owner_id'      => $user->id,
            'contact_email' => $request->contact_email,
            'phone'         => $request->phone,
            'city'          => $request->city,
            'gst_number'    => $request->gst_number,
            'plan'          => 'free',
            'seat_limit'    => 10,
            'seats_used'    => 1,
        ]);

        $user->update([
            'institute_id'   => $institute->id,
            'institute_role' => 'owner',
        ]);

        return redirect()->route('institute.dashboard')
            ->with('success', 'Institute registered successfully!');
    }

    // ─── Dashboard ────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $user      = Auth::user();
        $institute = $user->institute()->with('batches')->firstOrFail();

        if (!$user->isTeacher()) {
            abort(403, 'Teachers and owners only.');
        }

        $members    = User::where('institute_id', $institute->id)->with('tests')->get();
        $batches    = $institute->batches()->withCount('students')->get();
        $totalTests = $members->sum(fn($m) => $m->tests->count());

        return view('pages.institute.dashboard', compact('institute', 'members', 'batches', 'totalTests'));
    }

    // ─── Batch Management ─────────────────────────────────────────────────────

    public function batchCreate(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'test_type'   => 'required|in:academic,general',
            'target_band' => 'nullable|numeric|min:1|max:9',
            'exam_date'   => 'nullable|date|after:today',
            'description' => 'nullable|string|max:500',
        ]);

        $institute = Auth::user()->institute;

        Batch::create([
            'institute_id' => $institute->id,
            'name'         => $request->name,
            'test_type'    => $request->test_type,
            'target_band'  => $request->target_band,
            'exam_date'    => $request->exam_date,
            'description'  => $request->description,
        ]);

        return redirect()->route('institute.dashboard')
            ->with('success', "Batch '{$request->name}' created.");
    }

    public function batchAnalytics(Batch $batch)
    {
        $this->authorizeInstitute($batch->institute_id);
        $batch->load('institute');

        $students = $batch->students()->with(['tests' => fn($q) =>
            $q->whereNotNull('overall_band')
              ->where('status', 'completed')
              ->orderBy('created_at')
        ])->get();

        $modules = ['writing', 'speaking', 'listening', 'reading'];

        // Per-student latest band per module
        $studentRows = $students->map(function ($s) use ($modules) {
            $row = ['student' => $s, 'bands' => [], 'trend' => null, 'total_tests' => $s->tests->count()];
            foreach ($modules as $m) {
                $latest = $s->tests->where('type', $m)->sortByDesc('created_at')->first();
                $prev   = $s->tests->where('type', $m)->sortByDesc('created_at')->skip(1)->first();
                $band   = $latest?->overall_band;
                $row['bands'][$m] = [
                    'band'  => $band,
                    'delta' => ($band && $prev?->overall_band) ? round($band - $prev->overall_band, 1) : null,
                ];
            }
            // Overall trend: average of all module bands
            $allBands = collect($row['bands'])->pluck('band')->filter();
            $row['avg_band'] = $allBands->isNotEmpty() ? round($allBands->avg() * 2) / 2 : null;
            return $row;
        });

        // Batch averages per module
        $batchAverages = [];
        foreach ($modules as $m) {
            $bands = $studentRows->pluck("bands.{$m}.band")->filter();
            $batchAverages[$m] = $bands->isNotEmpty() ? round($bands->avg(), 1) : null;
        }

        // Score distribution (band 4-9 bucket counts) across all modules
        $distribution = [];
        foreach ($modules as $m) {
            $counts = array_fill_keys(['4','4.5','5','5.5','6','6.5','7','7.5','8','8.5','9'], 0);
            $students->each(function ($s) use ($m, &$counts) {
                $b = $s->tests->where('type', $m)->sortByDesc('created_at')->first()?->overall_band;
                if ($b) {
                    $key = (string) $b;
                    if (isset($counts[$key])) $counts[$key]++;
                }
            });
            $distribution[$m] = $counts;
        }

        // Weakest skill for the batch
        $scored = collect($batchAverages)->filter();
        $weakestSkill = $scored->isNotEmpty() ? $scored->sort()->keys()->first() : null;

        // Assignment completion rates for this batch
        $assignments = \App\Models\AssignedTest::where('batch_id', $batch->id)
            ->withCount(['studentRecords', 'studentRecords as completed_count' => fn($q) => $q->where('status', 'completed')])
            ->latest()->get();

        return view('pages.institute.batch-analytics', compact(
            'batch', 'students', 'studentRows', 'modules',
            'batchAverages', 'distribution', 'weakestSkill', 'assignments'
        ));
    }

    public function batchShow(Batch $batch)
    {
        $this->authorizeInstitute($batch->institute_id);

        $students = $batch->students()
            ->with(['tests' => fn($q) => $q->whereNotNull('overall_band')->latest()->limit(4)])
            ->get()
            ->map(function ($student) {
                $latestTests = $student->tests;
                return [
                    'user'     => $student,
                    'listening'=> optional($latestTests->firstWhere('type', 'listening_academic') ?? $latestTests->firstWhere('type', 'listening_general'))->overall_band,
                    'reading'  => optional($latestTests->firstWhere('type', 'reading_academic') ?? $latestTests->firstWhere('type', 'reading_general'))->overall_band,
                    'writing'  => optional($latestTests->firstWhere('type', 'writing_academic') ?? $latestTests->firstWhere('type', 'writing_general'))->overall_band,
                    'speaking' => optional($latestTests->firstWhere('type', 'speaking'))->overall_band,
                ];
            });

        return view('pages.institute.batch', compact('batch', 'students'));
    }

    // ─── Student Management ───────────────────────────────────────────────────

    public function inviteStudent(Request $request, Batch $batch)
    {
        $this->authorizeInstitute($batch->institute_id);

        $request->validate(['email' => 'required|email']);

        $institute = Auth::user()->institute;

        if (!$institute->hasSeatsAvailable()) {
            return back()->with('error', 'Seat limit reached. Upgrade your plan to add more students.');
        }

        $student = User::where('email', $request->email)->first();

        if (!$student) {
            // Create account with temp password and send invite
            $tempPassword = Str::random(12);
            $student = User::create([
                'name'               => explode('@', $request->email)[0],
                'email'              => $request->email,
                'password'           => Hash::make($tempPassword),
                'email_verified_at'  => now(),
                'institute_id'       => $institute->id,
                'institute_role'     => 'student',
                'test_credits'       => 3,
            ]);

            // Send invite email
            Mail::to($student)->send(new \App\Mail\StudentInviteMail($student, $institute, $batch, $tempPassword));
        } else {
            if ($student->institute_id && $student->institute_id !== $institute->id) {
                return back()->with('error', 'This student belongs to another institute.');
            }
            $student->update([
                'institute_id'   => $institute->id,
                'institute_role' => 'student',
            ]);
        }

        $batch->students()->syncWithoutDetaching([$student->id]);
        $institute->increment('seats_used');

        return back()->with('success', "Student {$request->email} added to batch.");
    }

    public function bulkImport(Request $request, Batch $batch)
    {
        $this->authorizeInstitute($batch->institute_id);

        $request->validate(['csv' => 'required|file|mimes:csv,txt|max:1024']);

        $institute = Auth::user()->institute;
        $file      = $request->file('csv');
        $rows      = array_map('str_getcsv', file($file->getRealPath()));
        $imported  = 0;
        $errors    = [];

        foreach ($rows as $i => $row) {
            if ($i === 0) continue; // skip header

            $email = trim($row[0] ?? '');
            $name  = trim($row[1] ?? explode('@', $email)[0]);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Row " . ($i + 1) . ": Invalid email '{$email}'";
                continue;
            }

            if (!$institute->hasSeatsAvailable()) {
                $errors[] = "Seat limit reached at row " . ($i + 1);
                break;
            }

            $tempPassword = Str::random(12);
            $student = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'              => $name,
                    'password'          => Hash::make($tempPassword),
                    'email_verified_at' => now(),
                    'test_credits'      => 3,
                ]
            );

            if (!$student->wasRecentlyCreated && $student->institute_id && $student->institute_id !== $institute->id) {
                $errors[] = "Row " . ($i + 1) . ": {$email} belongs to another institute.";
                continue;
            }

            $student->update(['institute_id' => $institute->id, 'institute_role' => 'student']);
            $batch->students()->syncWithoutDetaching([$student->id]);

            if ($student->wasRecentlyCreated) {
                Mail::to($student)->send(new \App\Mail\StudentInviteMail($student, $institute, $batch, $tempPassword));
                $institute->increment('seats_used');
            }

            $imported++;
        }

        $msg = "{$imported} students imported.";
        if ($errors) $msg .= ' ' . count($errors) . ' skipped.';

        return back()->with('success', $msg)->with('import_errors', $errors);
    }

    // ─── Question Bank ────────────────────────────────────────────────────────

    public function questionBank(Request $request)
    {
        $institute = $this->getInstituteOrFail();

        $query = Question::where('institute_id', $institute->id)->latest();

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $questions = $query->paginate(30)->withQueryString();
        $types     = ['writing', 'speaking', 'listening', 'reading'];

        return view('pages.institute.questions.index', compact('institute', 'questions', 'types'));
    }

    public function questionCreate()
    {
        $institute = $this->getInstituteOrFail();
        $this->authorizeTeacherRole();
        return view('pages.institute.questions.create', compact('institute'));
    }

    public function questionStore(Request $request)
    {
        $institute = $this->getInstituteOrFail();
        $this->authorizeTeacherRole();

        $data = $request->validate([
            'type'       => 'required|in:speaking,writing,listening,reading',
            'category'   => 'required|string',
            'title'      => 'required|string|max:500',
            'content'    => 'required|string',
            'media_url'  => 'nullable|url|max:500',
            'time_limit' => 'nullable|integer|min:1',
            'min_words'  => 'nullable|integer|min:1',
            'difficulty' => 'nullable|in:easy,medium,hard',
        ]);

        $metadata = [];
        if (!empty($data['difficulty'])) {
            $metadata['difficulty'] = $data['difficulty'];
        }
        unset($data['difficulty']);

        Question::create($data + ['institute_id' => $institute->id, 'metadata' => $metadata ?: null]);

        return redirect()->route('institute.questions.index')->with('success', 'Question created.');
    }

    public function questionEdit(Question $question)
    {
        $institute = $this->getInstituteOrFail();
        $this->authorizeTeacherRole();
        abort_if($question->institute_id !== $institute->id, 403);
        return view('pages.institute.questions.edit', compact('question', 'institute'));
    }

    public function questionUpdate(Request $request, Question $question)
    {
        $institute = $this->getInstituteOrFail();
        $this->authorizeTeacherRole();
        abort_if($question->institute_id !== $institute->id, 403);

        $data = $request->validate([
            'type'       => 'required|in:speaking,writing,listening,reading',
            'category'   => 'required|string',
            'title'      => 'required|string|max:500',
            'content'    => 'required|string',
            'media_url'  => 'nullable|url|max:500',
            'time_limit' => 'nullable|integer|min:1',
            'min_words'  => 'nullable|integer|min:1',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'active'     => 'boolean',
        ]);

        $metadata = $question->metadata ?? [];
        if (!empty($data['difficulty'])) {
            $metadata['difficulty'] = $data['difficulty'];
        }
        unset($data['difficulty']);

        $question->update($data + ['metadata' => $metadata]);

        return redirect()->route('institute.questions.index')->with('success', 'Question updated.');
    }

    public function questionDestroy(Question $question)
    {
        $institute = $this->getInstituteOrFail();
        $this->authorizeTeacherRole();
        abort_if($question->institute_id !== $institute->id, 403);
        $question->delete();
        return redirect()->route('institute.questions.index')->with('success', 'Question deleted.');
    }

    // ─── Question Sets (Institute-scoped) ────────────────────────────────────

    public function questionSets(Request $request)
    {
        $institute = $this->getInstituteOrFail();
        $sets = TestTemplate::where('institute_id', $institute->id)
            ->withCount('questions')->latest()->paginate(20);
        return view('pages.institute.question-sets.index', compact('institute', 'sets'));
    }

    public function questionSetCreate()
    {
        $institute = $this->getInstituteOrFail();
        $this->authorizeTeacherRole();
        return view('pages.institute.question-sets.create', compact('institute'));
    }

    public function questionSetStore(Request $request)
    {
        $institute = $this->getInstituteOrFail();
        $this->authorizeTeacherRole();

        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string|max:1000',
            'type'             => 'required|in:writing,speaking,listening,reading,full_mock',
            'duration_minutes' => 'required|integer|min:1|max:480',
        ]);

        $set = TestTemplate::create($data + [
            'institute_id' => $institute->id,
            'created_by'   => auth()->id(),
            'is_public'    => false,
            'is_active'    => true,
        ]);

        return redirect()->route('institute.question-sets.show', $set)
            ->with('success', 'Question set created. Now add questions to it.');
    }

    public function questionSetShow(TestTemplate $set)
    {
        $institute = $this->getInstituteOrFail();
        abort_if($set->institute_id !== $institute->id, 403);
        $set->load('questions');

        $availableQuestions = Question::where('institute_id', $institute->id)
            ->whereNotIn('id', $set->questions->pluck('id'))
            ->get();

        return view('pages.institute.question-sets.show', compact('institute', 'set', 'availableQuestions'));
    }

    public function questionSetAddQuestion(Request $request, TestTemplate $set)
    {
        $institute = $this->getInstituteOrFail();
        abort_if($set->institute_id !== $institute->id, 403);
        $this->authorizeTeacherRole();
        $request->validate(['question_id' => 'required|exists:questions,id']);

        $maxOrder = $set->questions()->max('template_questions.order') ?? 0;
        $set->questions()->syncWithoutDetaching([
            $request->question_id => ['order' => $maxOrder + 1],
        ]);

        return back()->with('success', 'Question added.');
    }

    public function questionSetRemoveQuestion(TestTemplate $set, Question $question)
    {
        $institute = $this->getInstituteOrFail();
        abort_if($set->institute_id !== $institute->id, 403);
        $this->authorizeTeacherRole();
        $set->questions()->detach($question->id);
        return back()->with('success', 'Question removed.');
    }

    public function questionSetDestroy(TestTemplate $set)
    {
        $institute = $this->getInstituteOrFail();
        abort_if($set->institute_id !== $institute->id, 403);
        $this->authorizeTeacherRole();
        $set->delete();
        return redirect()->route('institute.question-sets.index')->with('success', 'Question set deleted.');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function getInstituteOrFail(): Institute
    {
        $institute = Auth::user()->institute;
        abort_if(!$institute, 403, 'You do not belong to any institute.');
        return $institute;
    }

    private function authorizeTeacherRole(): void
    {
        if (!Auth::user()->isTeacher()) {
            abort(403, 'Teachers and owners only.');
        }
    }

    private function authorizeInstitute(int $instituteId): void
    {
        $user = Auth::user();
        if (!$user->isTeacher() || $user->institute_id !== $instituteId) {
            abort(403);
        }
    }
}
