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
        Schema::create('videoconferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users');
            $table->foreignId('studgroup_id')
                ->constrained('studgroups');
            $table->foreignId('test_id')
                ->constrained('tests');
            $table->timestamps();
            $table->dateTimeTz('date');
            $table->jsonb('settings')->default("{}");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videoconferences');
    }
};
