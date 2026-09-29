<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coworker_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('full_name');
            $table->string('phone_number');
            $table->string('email');
            $table->string('academic_year')->nullable();
            $table->string('department')->nullable();
            $table->string('location')->nullable();
            $table->text('reason_for_joining');
            $table->text('previous_experience')->nullable();
            $table->string('area_of_interest')->nullable();
            $table->text('additional_message')->nullable();

            $table->enum('status', ['pending', 'reviewed', 'accepted', 'rejected'])->default('pending');

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coworker_applications');
    }
};