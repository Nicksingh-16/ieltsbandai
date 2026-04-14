<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function show()
    {
        return view('pages.contact');
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|min:20|max:2000',
        ]);

        // Attempt to send email — gracefully skip if mail is not configured
        try {
            Mail::raw(
                "Name: {$validated['name']}\nEmail: {$validated['email']}\n\nSubject: {$validated['subject']}\n\nMessage:\n{$validated['message']}",
                function ($message) use ($validated) {
                    $message->to(config('mail.from.address', 'support@ieltsbandai.com'))
                            ->subject('Contact Form: ' . $validated['subject'])
                            ->replyTo($validated['email'], $validated['name']);
                }
            );
        } catch (\Exception $e) {
            // Mail not configured — log silently and continue
            \Log::info('Contact form submission (mail not sent): ' . json_encode($validated));
        }

        return redirect()->route('contact')
            ->with('success', "Thanks {$validated['name']}! We've received your message and will reply within 24 hours.");
    }
}
