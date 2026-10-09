<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Publish the day's Bible message at 6:00 AM, fellowship local time.
Schedule::command('messages:publish-morning')
    ->dailyAt('06:00')
    ->timezone('Africa/Addis_Ababa')
    ->withoutOverlapping();