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
        Schema::create('users_studgroups', function (Blueprint $table) {
            $table->foreignId('user_id')
            ->constrained('users');
            $table->foreignId('studgroup_id')
                ->constrained('studgroups');
            $table->primary(['user_id', 'studgroup_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_studgroups');
    }
};
