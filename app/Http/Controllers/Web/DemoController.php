<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\WritingScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class DemoController extends Controller
{
    // Fixed demo question — no DB dependency
    private function demoQuestion(): object
    {
        return (object) [
            'title'    => 'Some people believe that universities should focus on providing academic knowledge, while others think they should prepare students for employment. Discuss both views and give your own opinion.',
            'content'  => 'Some people believe that universities should focus on providing academic knowledge, while others think they should prepare students for employment. Discuss both views and give your own opinion.',
            'category' => 'writing_academic_task2',
            'type'     => 'writing',
            'min_words'  => 250,
            'time_limit' => 2400,
        ];
    }

    private const DEMO_COOKIE = 'demo_used';
    private const DEMO_COOKIE_DAYS = 30;

    public function index(Request $request)
    {
        // If this visitor already used the demo, send them back to the result
        // (session still alive) or show the locked gate.
        if ($request->cookie(self::DEMO_COOKIE)) {
            $scoring = session('demo_result');
            $answer  = session('demo_answer');
            if ($scoring) {
                return redirect()->route('demo.result');
            }
            // Session expired but cookie exists — show locked page
            $question = $this->demoQuestion();
            $locked = true;
            return view('pages.demo.index', compact('question', 'locked'));
        }

        $question = $this->demoQuestion();
        $locked = false;
        return view('pages.demo.index', compact('question', 'locked'));
    }

    public function submit(Request $request, WritingScoringService $scorer)
    {
        // Block repeat submissions
        if ($request->cookie(self::DEMO_COOKIE)) {
            $scoring = session('demo_result');
            if ($scoring) {
                return redirect()->route('demo.result');
            }
            return redirect()->route('demo')->with('error', 'You have already used the demo. Schedule a call to see the full product.');
        }

        $request->validate([
            'answer' => ['required', 'string', 'min:50', 'max:5000'],
        ]);

        $answer   = $request->input('answer');
        $question = $this->demoQuestion();

        $scoring = $scorer->scoreWriting($answer, $question);

        if (!$scoring) {
            return back()->withInput()->with('error', 'Scoring service is temporarily unavailable. Please try again in a moment.');
        }

        $overall = $scorer->calculateOverallBand($scoring);
        $scoring['overall_band'] = $overall;

        $demoMode = $request->input('demo_mode', 'practice') === 'exam' ? 'exam' : 'practice';
        session(['demo_result' => $scoring, 'demo_answer' => $answer, 'demo_mode' => $demoMode]);

        $cookie = Cookie::make(self::DEMO_COOKIE, '1', self::DEMO_COOKIE_DAYS * 24 * 60);

        return redirect()->route('demo.result')->withCookie($cookie);
    }

    public function result()
    {
        $scoring  = session('demo_result');
        $answer   = session('demo_answer');
        $demoMode = session('demo_mode', 'practice');

        if (!$scoring) {
            return redirect()->route('demo');
        }

        $question = $this->demoQuestion();

        return view('pages.demo.result', compact('scoring', 'answer', 'question', 'demoMode'));
    }
}
