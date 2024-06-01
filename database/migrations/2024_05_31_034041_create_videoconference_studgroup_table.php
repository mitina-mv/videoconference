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
        Schema::create('videoconference_studgroup', function (Blueprint $table) {
            $table->foreignId('vc_id')
                ->constrained('videoconferences');
            $table->foreignId('studgroup_id')
                ->constrained('studgroups');
            $table->primary(['vc_id', 'studgroup_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videoconference_studgroup');
    }
};
