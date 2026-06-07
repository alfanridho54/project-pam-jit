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
        if (Schema::hasColumn('jit_sessions', 'expiry_warning_sent_at')) {
            return;
        }

        Schema::table('jit_sessions', function (Blueprint $table) {
            $table->timestamp('expiry_warning_sent_at')->nullable()->after('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('jit_sessions', 'expiry_warning_sent_at')) {
            return;
        }

        Schema::table('jit_sessions', function (Blueprint $table) {
            $table->dropColumn('expiry_warning_sent_at');
        });
    }
};
