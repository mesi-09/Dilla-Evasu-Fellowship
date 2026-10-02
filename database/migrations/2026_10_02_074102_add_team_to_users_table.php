<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('team', [
                'prayer',
                'counseling',
                'art',
                'teaching_and_training',
                'evangelism',
                'worship',
                'love_and_sharing',
                'choir',
                'found',
                'meleket_media',
                'none',
            ])->default('none')->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('team');
        });
    }
};