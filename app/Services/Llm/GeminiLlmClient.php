<?php

namespace App\Services\Llm;

use Illuminate\Support\Facades\Http;
use Throwable;

class GeminiLlmClient implements LlmClient
{
    public function rewriteAnswer(string $question, string $answer, string $locale): string
    {
        try {
            $response = Http::connectTimeout(3)
                ->timeout(12)
                ->acceptJson()
                ->post(sprintf(
                    'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
                    rawurlencode((string) config('services.gemini.model')),
                    rawurlencode((string) config('services.gemini.key')),
                ), [
                    'system_instruction' => [
                        'parts' => [[
                            'text' => 'Rewrite the supplied support answer naturally in the requested language. Preserve all facts, links, dates, and policy details. Do not add facts. Return only the rewritten answer.',
                        ]],
                    ],
                    'contents' => [[
                        'role' => 'user',
                        'parts' => [[
                            'text' => "Language: {$locale}\nVisitor question: {$question}\nVerified FAQ answer: {$answer}",
                        ]],
                    ]],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'maxOutputTokens' => 300,
                    ],
                ]);

            $rewritten = $response->json('candidates.0.content.parts.0.text');

            return $response->successful() && is_string($rewritten) && trim($rewritten) !== ''
                ? trim($rewritten)
                : $answer;
        } catch (Throwable) {
            return $answer;
        }
    }
}
