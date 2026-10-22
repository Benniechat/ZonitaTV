<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:190',
            'topics' => 'nullable|array',
        ]);

        $subscription = NewsletterSubscription::firstOrCreate(
            ['email' => strtolower(trim($validated['email']))],
            [
                'topics' => $validated['topics'] ?? ['general_guide', 'sports', 'news'],
                'is_active' => true,
                'unsubscribe_token' => Str::random(40),
            ]
        );

        if (! $subscription->wasRecentlyCreated) {
            $subscription->update([
                'is_active' => true,
                'unsubscribed_at' => null,
                'topics' => $validated['topics'] ?? $subscription->topics,
            ]);
        }

        return back()->with('success', 'You have been subscribed to the Zoniva TV Viewing Guide!');
    }
}
