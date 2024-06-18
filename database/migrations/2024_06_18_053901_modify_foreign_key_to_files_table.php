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
        Schema::table('files', function (Blueprint $table) {
            $table->dropForeign(['videoconference_id']);

            $table->foreign('videoconference_id')
                ->references('id')->on('videoconferences')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropForeign(['videoconference_id']);

            $table->foreign('videoconference_id')
                ->references('id')->on('videoconferences');
        });
    }
};
