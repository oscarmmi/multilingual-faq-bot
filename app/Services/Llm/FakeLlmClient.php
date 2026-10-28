<?php

namespace App\Services\Llm;

class FakeLlmClient implements LlmClient
{
    public ?string $response = null;

    public function rewriteAnswer(string $question, string $answer, string $locale): string
    {
        return $this->response ?? $answer;
    }
}
