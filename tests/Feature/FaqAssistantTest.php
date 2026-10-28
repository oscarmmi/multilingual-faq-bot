<?php

use App\Models\FaqTurn;
use App\Services\FaqAssistant;
use App\Services\Llm\FakeLlmClient;
use App\Services\Llm\LlmClient;
use Database\Seeders\DatabaseSeeder;

it('answers a Spanish shipping question from the Spanish FAQ and stores the detected locale', function () {
    $this->seed(DatabaseSeeder::class);

    $response = $this->postJson('/ask', [
        'question' => '¿Dónde está mi pedido y cuánto tarda el envío?',
    ]);

    $response->assertOk()
        ->assertJsonPath('locale', 'es')
        ->assertJsonPath('matched_slug', 'shipping')
        ->assertJsonPath('answer', fn (string $answer): bool => str_contains($answer, 'envío'));

    expect(FaqTurn::query()->latest('id')->first())
        ->locale->toBe('es')
        ->matched_slug->toBe('shipping');
});

it('answers an English hours question from the English FAQ', function () {
    $this->seed(DatabaseSeeder::class);

    $this->postJson('/ask', ['question' => 'What are your support hours?'])
        ->assertOk()
        ->assertJsonPath('locale', 'en')
        ->assertJsonPath('matched_slug', 'hours')
        ->assertJsonPath('answer', fn (string $answer): bool => str_contains($answer, 'Monday through Friday'));
});

it('binds the fake LLM client when no Gemini key is configured', function () {
    config(['services.gemini.key' => null]);

    expect(app(LlmClient::class))->toBeInstanceOf(FakeLlmClient::class);
});

it('keeps the verified Spanish answer if a rewrite returns English copy', function () {
    $this->seed(DatabaseSeeder::class);
    $fake = new FakeLlmClient;
    $fake->response = 'Your package ships in three to five business days.';
    $this->app->instance(LlmClient::class, $fake);

    $result = app(FaqAssistant::class)->answer('¿Cuánto tarda el envío de mi pedido?');

    expect($result['locale'])->toBe('es')
        ->and($result['answer'])->toContain('días laborables')
        ->and($result['answer'])->not->toContain('package');
});

it('allows a guest to submit a question successfully', function () {
    $this->seed(DatabaseSeeder::class);

    $this->postJson('/ask', ['question' => 'Where is my order?'])
        ->assertOk()
        ->assertJsonPath('locale', 'en');
});
