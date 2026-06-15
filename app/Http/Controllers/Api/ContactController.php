<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactFormMail;
use App\Models\ContactMessage;
use App\Models\SeoSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class ContactController extends Controller
{
    /**
     * POST /api/contact
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'subject' => 'nullable|string|max:200',
            'message' => 'required|string|min:10|max:3000',
        ]);

        // Rate limit: 3 messages per IP per hour
        $key = 'contact_' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return response()->json([
                'message' => 'Too many messages sent. Please try again later.',
            ], 429);
        }
        RateLimiter::hit($key, 3600);

        // Save to DB
        $message = ContactMessage::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'subject'    => $request->subject,
            'message'    => $request->message,
            'ip_address' => $request->ip(),
        ]);

        // Send email notification to site owner
        try {
            Mail::to(config('mail.to_address', 'gayan622@email.com'))
                ->send(new ContactFormMail($message));
        } catch (\Exception $e) {
            // Email failure should not fail the request — message is saved
        }

        return response()->json([
            'message' => 'Your message has been received. I\'ll reply within 24 hours.',
        ]);
    }
}

// ─────────────────────────────────────────────────────────────────────────────

class SeoController extends Controller
{
    /**
     * GET /api/seo/{page}
     */
    public function show(string $page): JsonResponse
    {
        $seo = SeoSetting::where('page', $page)->first();

        if (!$seo) {
            // Return sensible defaults if not configured yet
            $defaults = [
                'page'        => $page,
                'title'       => 'Gayan622 Online — Web Developer & Designer',
                'description' => 'Web Developer & Designer with 15+ years of experience. Based in Negombo, Sri Lanka.',
                'keywords'    => 'web developer sri lanka, laravel developer, next.js developer',
                'og_image'    => null,
            ];
            return response()->json(['data' => $defaults]);
        }

        return response()->json(['data' => $seo]);
    }
}
