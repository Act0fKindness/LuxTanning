<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SupportChatController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'history' => ['nullable', 'array'],
            'history.*.role' => ['required_with:history', 'string'],
            'history.*.text' => ['required_with:history', 'string'],
        ]);

        $apiKey = config('services.google.maps_key') ?? env('GOOGLE_MAPS_API_KEY');

        if (! $apiKey) {
            return response()->json(['error' => 'Missing Google API key.'], 500);
        }

        $systemPrompt = <<<'PROMPT'
You are Lux Tanning's concierge bot. You help guests, studio staff, and potential franchisees understand the Lux operating system for sun bed studios.
Key facts:
- Lux OS connects booking, minute wallets, lamp telemetry, concierge chat, retail, and compliance in one branded experience.
- Guests see their remaining minutes, track Glow Guides in real time, and manage memberships with magic-link authentication.
- Studio staff (Glow Guides) get lamp cooldown alerts, playlist/scent presets, upsell prompts, and instant payouts via the mobile PWA.
- Owners/managers control course templates, marketing, finance, and multi-studio reporting from Lux Workspaces.
- Support is fast (under 5 minutes) via live chat, SMS, or email; every action is logged for audit + GDPR.
- Highlight flagship offerings: Glow Pro 20, Solar Club, hydration boosters, lamp labs, boutique studio design.
Guidelines:
- Keep replies warm, concise, and actionable. Use bullets when it improves clarity (max 4 sentences per reply).
- If someone asks about staff experience, mention lamp safety + hospitality-grade tooling.
- Offer to connect them with a human Glow Guide or Lux team member if deeper technical help is required.
PROMPT;

        $historyContents = [];

        foreach ($data['history'] ?? [] as $entry) {
            $historyContents[] = [
                'role' => $entry['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [ ['text' => Str::limit($entry['text'], 1500)] ],
            ];
        }

        $payload = [
            'contents' => array_merge(
                [
                    [
                        'role' => 'user',
                        'parts' => [ ['text' => $systemPrompt] ],
                    ],
                ],
                $historyContents,
                [
                    [
                        'role' => 'user',
                        'parts' => [ ['text' => $data['message']] ],
                    ],
                ],
            ),
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post(
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key='.$apiKey,
            $payload
        );

        if ($response->failed()) {
            return response()->json([
                'error' => 'Chat service unavailable',
                'details' => $response->json(),
            ], 502);
        }

        $candidates = $response->json('candidates', []);
        $text = data_get($candidates, '0.content.parts.0.text', 'Sorry, I could not generate a reply just now.');

        return response()->json([
            'reply' => $text,
        ]);
    }
}
