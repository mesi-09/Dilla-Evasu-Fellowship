<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title');
            $table->text('body');
            $table->enum('status', ['draft', 'published'])->default('draft');

            // "Important" announcements notify every active member once.
            $table->boolean('is_important')->default(false);
            $table->timestamp('published_at')->nullable();
            // Set when members were notified, so an edit can never notify twice.
            $table->timestamp('notified_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};