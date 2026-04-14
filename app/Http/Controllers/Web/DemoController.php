<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\WritingScoringService;
use Illuminate\Http\Request;

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

    public function index()
    {
        $question = $this->demoQuestion();
        return view('pages.demo.index', compact('question'));
    }

    public function submit(Request $request, WritingScoringService $scorer)
    {
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

        session(['demo_result' => $scoring, 'demo_answer' => $answer]);

        return redirect()->route('demo.result');
    }

    public function result()
    {
        $scoring = session('demo_result');
        $answer  = session('demo_answer');

        if (!$scoring) {
            return redirect()->route('demo');
        }

        $question = $this->demoQuestion();

        return view('pages.demo.result', compact('scoring', 'answer', 'question'));
    }
}
