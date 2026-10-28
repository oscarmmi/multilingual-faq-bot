<?php

use App\Models\FaqItem;
use App\Services\FaqAssistant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return inertia('Welcome', [
        'faqs' => FaqItem::query()
            ->where('locale', 'en')
            ->orderBy('id')
            ->get(['slug', 'question'])
            ->map(fn (FaqItem $faq): array => ['slug' => $faq->slug, 'question' => $faq->question]),
        'llmEnabled' => filled(config('services.gemini.key')),
    ]);
})->name('home');

Route::post('/ask', function (Request $request, FaqAssistant $assistant) {
    $validated = $request->validate([
        'question' => ['required', 'string', 'min:2', 'max:1000'],
    ]);

    return response()->json($assistant->answer($validated['question']));
})->middleware('throttle:30,1')->name('ask');
