<?php

namespace App\Services\Llm;

interface LlmClient
{
    public function rewriteAnswer(string $question, string $answer, string $locale): string;
}
