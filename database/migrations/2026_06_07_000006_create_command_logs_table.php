<?php

use App\Models\CommandLog;
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
        if (Schema::hasTable('command_logs')) {
            return;
        }

        Schema::create('command_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jit_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('target_server_id')->constrained()->cascadeOnDelete();
            $table->text('command');
            $table->enum('status', CommandLog::statuses());
            $table->longText('output_excerpt')->nullable();
            $table->integer('exit_code')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->index(['jit_session_id', 'executed_at']);
            $table->index(['user_id', 'executed_at']);
            $table->index(['target_server_id', 'executed_at']);
            $table->index(['status', 'executed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('command_logs');
    }
};
