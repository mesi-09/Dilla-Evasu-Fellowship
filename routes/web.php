<?php

use App\Http\Controllers\Admin\BibleMessageController as AdminBibleMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProhibitedWordController;
use App\Http\Controllers\BibleMessageController;
use App\Http\Controllers\CommunityCommentController;
use App\Http\Controllers\CommunityPostController;
use App\Http\Controllers\CommunityReactionController;
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

// Public — anyone can read published Bible messages.
Route::get('bible-messages', [BibleMessageController::class, 'index'])
    ->name('bible-messages.index');
Route::get('bible-messages/{bibleMessage}', [BibleMessageController::class, 'show'])
    ->name('bible-messages.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:main_admin'])->group(function () {
    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/members', [DashboardController::class, 'members'])->name('admin.members');

    Route::get('coworker-applications', [CoworkerApplicationController::class, 'index'])
        ->name('coworker-applications.index');
    Route::get('coworker-applications/{coworkerApplication}', [CoworkerApplicationController::class, 'show'])
        ->name('coworker-applications.show');
    Route::put('coworker-applications/{coworkerApplication}', [CoworkerApplicationController::class, 'update'])
        ->name('coworker-applications.update');

    Route::get('admin/prohibited-words', [ProhibitedWordController::class, 'index'])
        ->name('admin.prohibited-words.index');
    Route::post('admin/prohibited-words', [ProhibitedWordController::class, 'store'])
        ->name('admin.prohibited-words.store');
    Route::patch('admin/prohibited-words/{prohibitedWord}/toggle', [ProhibitedWordController::class, 'toggle'])
        ->name('admin.prohibited-words.toggle');
    Route::delete('admin/prohibited-words/{prohibitedWord}', [ProhibitedWordController::class, 'destroy'])
        ->name('admin.prohibited-words.destroy');

    Route::get('admin/bible-messages', [AdminBibleMessageController::class, 'index'])
        ->name('admin.bible-messages.index');
    Route::get('admin/bible-messages/create', [AdminBibleMessageController::class, 'create'])
        ->name('admin.bible-messages.create');
    Route::post('admin/bible-messages', [AdminBibleMessageController::class, 'store'])
        ->name('admin.bible-messages.store');
    Route::get('admin/bible-messages/{bibleMessage}/edit', [AdminBibleMessageController::class, 'edit'])
        ->name('admin.bible-messages.edit');
    Route::put('admin/bible-messages/{bibleMessage}', [AdminBibleMessageController::class, 'update'])
        ->name('admin.bible-messages.update');
    Route::delete('admin/bible-messages/{bibleMessage}', [AdminBibleMessageController::class, 'destroy'])
        ->name('admin.bible-messages.destroy');
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

    Route::get('community', [CommunityPostController::class, 'index'])->name('community.index');
    Route::post('community', [CommunityPostController::class, 'store'])->name('community.store');
    Route::delete('community/{communityPost}', [CommunityPostController::class, 'destroy'])->name('community.destroy');

    Route::post('community/{communityPost}/comments', [CommunityCommentController::class, 'store'])
        ->name('community.comments.store');
    Route::delete('community/comments/{communityComment}', [CommunityCommentController::class, 'destroy'])
        ->name('community.comments.destroy');

    Route::post('community/{communityPost}/reactions/toggle', [CommunityReactionController::class, 'toggle'])
        ->name('community.reactions.toggle');
});

require __DIR__.'/auth.php';