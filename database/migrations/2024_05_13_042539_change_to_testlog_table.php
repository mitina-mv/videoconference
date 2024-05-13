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
        Schema::table('testlogs', function (Blueprint $table) {
            $table->dropColumn('date');
            $table->dropColumn('test_id');
            $table->dropColumn('teacher_id');
            $table->dropColumn('student_id');

            $table->foreignId('user_id')
                ->constrained('users');

            $table->foreignId('assignment_id')
                ->constrained('assignments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('testlogs', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->dropColumn('assignment_id');

            $table->dateTimeTz('date');

            $table->foreignId('test_id')
                ->constrained('tests');
            $table->foreignId('teacher_id')
                ->constrained('users');
            $table->foreignId('student_id')
                ->constrained('users');
        });
    }
};
