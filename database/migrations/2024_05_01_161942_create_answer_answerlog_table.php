<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('answers_answerlogs', function (Blueprint $table) {
            $table->foreignId('answer_id')
                ->constrained('answers');
            $table->foreignId('answerlog_id')
                ->constrained('answerlogs');
            $table->primary(['answer_id', 'answerlog_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answer_answerlog');
    }
};
