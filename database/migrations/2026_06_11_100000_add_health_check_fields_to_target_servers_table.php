<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('target_servers', function (Blueprint $table) {
            $table->string('last_health_status')->nullable()->after('is_active');
            $table->timestamp('last_health_checked_at')->nullable()->after('last_health_status');
            $table->unsignedInteger('last_health_latency_ms')->nullable()->after('last_health_checked_at');
            $table->text('last_health_message')->nullable()->after('last_health_latency_ms');
        });
    }

    public function down(): void
    {
        Schema::table('target_servers', function (Blueprint $table) {
            $table->dropColumn([
                'last_health_status',
                'last_health_checked_at',
                'last_health_latency_ms',
                'last_health_message',
            ]);
        });
    }
};
