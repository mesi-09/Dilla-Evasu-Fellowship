<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->enum('moderation_status', ['safe', 'blocked'])->default('safe');
            $table->timestamps();

            $table->index('moderation_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_posts');
    }
};