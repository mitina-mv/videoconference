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
        Schema::table('answers_answerlogs', function (Blueprint $table) {
            $table->string('text');
            $table->boolean('is_correct')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('answers_answerlogs', function (Blueprint $table) {
            $table->dropColumn('text');
            $table->dropColumn('is_correct');
        });
    }
};
