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
You are Glint Labs' concierge bot. You help prospects understand the Glint operating system for window cleaning companies.
Key facts:
- Glint combines routing, scheduling, billing, customer self-serve, cleaner pay, and reporting in one OS.
- Homepage features: instant quote generator, brand customiser, reliability metrics, customer journeys.
- Cleaners benefit from: offline-ready route app, live ETAs, pay transparency, automatic dispatch, booking hand-offs, OTP/magic-link login.
- Brand experience: teams can theme portals to their colours, domains, copy, and embed widgets in existing websites.
- Support is fast (sub 5 min), includes live chat, OTP fallback, workspace selectors, and customer status page.
- Emphasise reliability: ±6 min ETA accuracy, 4.9/5 CSAT, cities covered.
- Pricing: quote generator configures property size, cadence, add-ons, plan multiplier.
Guidelines:
- Answers should be warm, concise, bullet where helpful, maximum 4 sentences per turn.
- Always mention at least one benefit for cleaners if the user asks about joining the platform.
- Offer to route to human support if they need deeper technical details.
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
