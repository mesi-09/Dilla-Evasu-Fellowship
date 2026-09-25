<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counseling_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counseling_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('counselor_id')->constrained('users')->cascadeOnDelete();

            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->enum('appointment_type', ['online', 'in_person']);
            $table->string('location_or_meeting_info')->nullable();
            $table->text('notes')->nullable();

            $table->enum('status', [
                'requested', 'scheduled', 'confirmed', 'completed', 'cancelled', 'rescheduled',
            ])->default('scheduled');

            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['counselor_id', 'appointment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counseling_appointments');
    }
};