<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jit_sessions', function (Blueprint $table) {
            $table->boolean('uses_temporary_credential')->default(false)->after('revoke_reason');
            $table->string('temporary_username')->nullable()->after('uses_temporary_credential');
            $table->text('temporary_password_encrypted')->nullable()->after('temporary_username');
            $table->string('temporary_credential_status')->nullable()->after('temporary_password_encrypted');
            $table->timestamp('temporary_credential_created_at')->nullable()->after('temporary_credential_status');
            $table->timestamp('temporary_credential_disabled_at')->nullable()->after('temporary_credential_created_at');
            $table->timestamp('temporary_credential_deleted_at')->nullable()->after('temporary_credential_disabled_at');
            $table->text('temporary_credential_error')->nullable()->after('temporary_credential_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('jit_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'uses_temporary_credential',
                'temporary_username',
                'temporary_password_encrypted',
                'temporary_credential_status',
                'temporary_credential_created_at',
                'temporary_credential_disabled_at',
                'temporary_credential_deleted_at',
                'temporary_credential_error',
            ]);
        });
    }
};
