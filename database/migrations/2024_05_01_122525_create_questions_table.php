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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_private')->default(0);
            $table->string('text', 511);
            $table->jsonb('settings')->default("{}");
            $table->foreignId('org_id')
                ->constrained('orgs');
            $table->foreignId('user_id')
                ->constrained('users');
            $table->foreignId('discipline_id')
                ->constrained('disciplines');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
