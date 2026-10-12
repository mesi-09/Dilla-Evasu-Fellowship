<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            // Who did it. Nulled if that user is deleted, so the entry survives.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // A snapshot of their name at the time, so the entry still reads
            // correctly after the account is gone or renamed.
            $table->string('actor_name');
            $table->string('action');
            $table->string('description', 500);
            // Entries are never edited, so there is no updated_at.
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};