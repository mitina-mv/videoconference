<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign(['vc_id']);

            $table->foreign('vc_id')
                ->references('id')->on('videoconferences')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign(['vc_id']);

            $table->foreign('vc_id')
                ->references('id')->on('videoconferences');
        });
    }
};
