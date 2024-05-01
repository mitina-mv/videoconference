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
        Schema::create('testlogs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->dateTimeTz('date');
            $table->decimal('mark')->nullable();
            $table->timestamp('time')->nullable();
            $table->foreignId('student_id')
                ->constrained('users');
            $table->foreignId('test_id')
                ->constrained('tests');
            $table->foreignId('teacher_id')
                ->constrained('users');
            $table->json('uncorrect_answers')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testlogs');
    }
};
