<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bible_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title');
            $table->string('verse');
            $table->text('message');
            $table->string('author')->nullable();

            // The day this message should go out. The scheduler publishes
            // any 'scheduled' message whose date has arrived.
            $table->date('publish_date')->nullable();
            $table->timestamp('published_at')->nullable();

            $table->enum('status', ['draft', 'scheduled', 'published'])->default('draft');

            $table->timestamps();

            $table->index(['status', 'publish_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bible_messages');
    }
};