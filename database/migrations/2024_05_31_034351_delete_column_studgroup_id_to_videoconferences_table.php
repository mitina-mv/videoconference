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
        Schema::table('videoconferences', function (Blueprint $table) {
            $table->dropColumn('studgroup_id');
            $table->string('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videoconferences', function (Blueprint $table) {
            $table->foreignId('studgroup_id')->nullable()
                ->constrained('studgroups');
            $table->dropColumn('name');
        });
    }
};
