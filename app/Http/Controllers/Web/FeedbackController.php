<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Services\EventTracker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function create(Request $request)
    {
        return view('pages.feedback.create', [
            'page_url' => $request->headers->get('referer'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => 'required|in:bug,feature,scoring,general',
            'rating'   => 'nullable|integer|min:1|max:5',
            'message'  => 'required|string|min:10|max:4000',
            'email'    => 'nullable|email|max:255',
            'page_url' => 'nullable|string|max:500',
        ]);

        $feedback = Feedback::create([
            'user_id'    => Auth::id(),
            'email'      => $data['email'] ?? optional(Auth::user())->email,
            'category'   => $data['category'],
            'rating'     => $data['rating'] ?? null,
            'message'    => $data['message'],
            'page_url'   => $data['page_url'] ?? null,
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'ip'         => $request->ip(),
        ]);

        if (class_exists(EventTracker::class)) {
            app(EventTracker::class)->track('feedback_submitted', [
                'category' => $feedback->category,
                'rating'   => $feedback->rating,
            ], Auth::user());
        }

        return redirect()->route('feedback.thanks');
    }

    public function thanks()
    {
        return view('pages.feedback.thanks');
    }
}
