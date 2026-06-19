<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('command_logs', function (Blueprint $table) {
            $table->text('blocked_reason')->nullable()->after('status');
            $table->json('metadata')->nullable()->after('exit_code');
        });
    }

    public function down(): void
    {
        Schema::table('command_logs', function (Blueprint $table) {
            $table->dropColumn(['blocked_reason', 'metadata']);
        });
    }
};
