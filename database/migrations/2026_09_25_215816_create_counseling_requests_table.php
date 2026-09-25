<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counseling_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_leader_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('full_name');
            $table->string('phone_number');
            $table->string('email');
            $table->string('academic_year')->nullable();
            $table->string('department')->nullable();
            $table->string('university')->nullable();
            $table->string('location')->nullable();
            $table->text('description');
            $table->enum('counseling_type', ['online', 'in_person']);
            $table->string('preferred_contact_method')->nullable();
            $table->string('availability')->nullable();

            $table->enum('status', [
                'pending', 'reviewed', 'accepted', 'in_progress', 'completed', 'rejected', 'cancelled',
            ])->default('pending');

            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index('assigned_leader_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counseling_requests');
    }
};