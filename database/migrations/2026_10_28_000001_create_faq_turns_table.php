<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faq_turns', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 2);
            $table->text('question');
            $table->text('answer');
            $table->string('matched_slug')->nullable();
            $table->timestamps();
            $table->index(['locale', 'matched_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faq_turns');
    }
};
