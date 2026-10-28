<?php

namespace App\Services;

use App\Models\FaqItem;
use App\Models\FaqTurn;
use App\Services\Llm\LlmClient;
use Illuminate\Support\Str;
use Throwable;

class FaqAssistant
{
    /** @var array<string, array<string, list<string>>> */
    private const KEYWORDS = [
        'shipping' => [
            'en' => ['ship', 'shipping', 'delivery', 'deliver', 'order', 'package', 'tracking', 'track'],
            'es' => ['envio', 'enviar', 'envias', 'pedido', 'paquete', 'entrega', 'entregar', 'seguimiento', 'rastrear'],
        ],
        'returns' => [
            'en' => ['return', 'returns', 'refund', 'exchange', 'send back'],
            'es' => ['devolucion', 'devolver', 'devoluciones', 'reembolso', 'cambio', 'retornar'],
        ],
        'hours' => [
            'en' => ['hour', 'hours', 'open', 'opening', 'close', 'closing', 'available', 'when', 'time'],
            'es' => ['horario', 'horarios', 'abierto', 'abren', 'cierra', 'cerrado', 'disponible', 'cuando', 'hora'],
        ],
        'contact' => [
            'en' => ['contact', 'support', 'help', 'email', 'reach', 'talk'],
            'es' => ['contactar', 'contacto', 'atencion', 'ayuda', 'correo', 'escribir', 'hablar'],
        ],
    ];

    public function __construct(private readonly LlmClient $llm) {}

    /** @return array{locale: string, answer: string, matched_slug: ?string, turn_id: int} */
    public function answer(string $question): array
    {
        $locale = $this->detectLocale($question);
        $faq = $this->findFaq($question, $locale);
        $answer = $faq !== null
            ? $faq->answer
            : ($locale === 'es'
                ? 'No encuentro una respuesta exacta en nuestras preguntas frecuentes. Escribe a support@harbordesk.example y te ayudaremos durante nuestro horario de atención.'
                : 'I couldn’t find an exact answer in our FAQs. Email support@harbordesk.example and we’ll help during support hours.');

        try {
            $rewritten = $this->llm->rewriteAnswer($question, $answer, $locale);

            if ($this->detectLocale($rewritten) === $locale) {
                $answer = $rewritten;
            }
        } catch (Throwable) {
            // A rewrite is optional: the verified FAQ answer remains safe to return.
        }

        $turn = FaqTurn::query()->create([
            'locale' => $locale,
            'question' => $question,
            'answer' => $answer,
            'matched_slug' => $faq?->slug,
        ]);

        return [
            'locale' => $locale,
            'answer' => $answer,
            'matched_slug' => $faq?->slug,
            'turn_id' => $turn->id,
        ];
    }

    public function detectLocale(string $question): string
    {
        $text = Str::lower(Str::ascii($question));
        $spanishSignals = preg_match('/\b(el|la|los|las|un|una|que|como|donde|cuando|cuanto|mi|mis|envio|pedido|devolucion|horario|atencion|ayuda|puedo|esta|estan|por|favor|gracias)\b/', $text) === 1;

        return $spanishSignals ? 'es' : 'en';
    }

    private function findFaq(string $question, string $locale): ?FaqItem
    {
        $text = Str::lower(Str::ascii($question));
        $bestSlug = null;
        $bestScore = 0;

        foreach (self::KEYWORDS as $slug => $translations) {
            $score = 0;

            foreach ($translations[$locale] as $keyword) {
                $term = preg_quote($keyword, '/');
                $score += preg_match('/(?<![a-z])'.$term.'(?![a-z])/', $text) === 1 ? 2 : 0;
            }

            if ($score > $bestScore) {
                $bestSlug = $slug;
                $bestScore = $score;
            }
        }

        return $bestSlug === null
            ? null
            : FaqItem::query()->where('slug', $bestSlug)->where('locale', $locale)->first();
    }
}
