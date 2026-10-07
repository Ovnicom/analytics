<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('msp_upload_batches', function (Blueprint $table) {
            $table->string('refresh_status')->nullable()->index();
            $table->text('refresh_message')->nullable();
            $table->timestamp('refresh_started_at')->nullable();
            $table->timestamp('refresh_finished_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('msp_upload_batches', function (Blueprint $table) {
            $table->dropIndex(['refresh_status']);
            $table->dropColumn([
                'refresh_status',
                'refresh_message',
                'refresh_started_at',
                'refresh_finished_at',
            ]);
        });
    }
};
