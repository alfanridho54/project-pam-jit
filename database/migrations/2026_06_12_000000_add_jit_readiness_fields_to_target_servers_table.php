<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('target_servers', function (Blueprint $table) {
            $table->string('last_jit_readiness_status')->nullable()->after('last_health_message');
            $table->timestamp('last_jit_readiness_checked_at')->nullable()->after('last_jit_readiness_status');
            $table->text('last_jit_readiness_message')->nullable()->after('last_jit_readiness_checked_at');
            $table->json('last_jit_readiness_details')->nullable()->after('last_jit_readiness_message');
        });
    }

    public function down(): void
    {
        Schema::table('target_servers', function (Blueprint $table) {
            $table->dropColumn([
                'last_jit_readiness_status',
                'last_jit_readiness_checked_at',
                'last_jit_readiness_message',
                'last_jit_readiness_details',
            ]);
        });
    }
};
