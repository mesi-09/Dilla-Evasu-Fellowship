<?php

use App\Http\Controllers\CounselingAppointmentController;
use App\Http\Controllers\CounselingMessageController;
use App\Http\Controllers\CounselingRequestController;
use App\Http\Controllers\CoworkerApplicationController;
use App\Http\Controllers\LoveSharingMessageController;
use App\Http\Controllers\LoveSharingRequestController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Public — anyone (student or visitor) can apply, no login required.
Route::get('coworker-applications/apply', [CoworkerApplicationController::class, 'create'])
    ->name('coworker-applications.create');
Route::post('coworker-applications', [CoworkerApplicationController::class, 'store'])
    ->name('coworker-applications.store');
Route::get('coworker-applications/thank-you', [CoworkerApplicationController::class, 'thankYou'])
    ->name('coworker-applications.thank-you');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:main_admin'])->group(function () {
    Route::get('/admin', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('coworker-applications', [CoworkerApplicationController::class, 'index'])
        ->name('coworker-applications.index');
    Route::get('coworker-applications/{coworkerApplication}', [CoworkerApplicationController::class, 'show'])
        ->name('coworker-applications.show');
    Route::put('coworker-applications/{coworkerApplication}', [CoworkerApplicationController::class, 'update'])
        ->name('coworker-applications.update');
});

Route::middleware('auth')->group(function () {
    Route::resource('love-sharing', LoveSharingRequestController::class)
        ->except(['edit'])
        ->parameters(['love-sharing' => 'loveSharingRequest']);

    Route::get('love-sharing/{loveSharingRequest}/messages', [LoveSharingMessageController::class, 'index'])
        ->name('love-sharing.messages.index');
    Route::post('love-sharing/{loveSharingRequest}/messages', [LoveSharingMessageController::class, 'store'])
        ->name('love-sharing.messages.store');

    Route::resource('counseling', CounselingRequestController::class)
        ->except(['edit'])
        ->parameters(['counseling' => 'counselingRequest']);

    Route::get('counseling/{counselingRequest}/messages', [CounselingMessageController::class, 'index'])
        ->name('counseling.messages.index');
    Route::post('counseling/{counselingRequest}/messages', [CounselingMessageController::class, 'store'])
        ->name('counseling.messages.store');

    Route::resource('counseling-appointments', CounselingAppointmentController::class)
        ->except(['edit'])
        ->parameters(['counseling-appointments' => 'counselingAppointment']);
});

require __DIR__.'/auth.php';