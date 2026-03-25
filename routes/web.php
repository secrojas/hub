<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => false,
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('dashboard');

    Route::get('/invitations/create', [InvitationController::class, 'create'])->name('invitations.create');
    Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');

    Route::resource('clients', ClientController::class);

    Route::resource('tasks', TaskController::class)->except(['show', 'create', 'edit']);
    Route::put('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');

    Route::resource('billing', BillingController::class)->except(['show']);
});

// Public invitation acceptance (requires valid signature)
Route::middleware('signed')->group(function () {
    Route::get('/invitation/accept', [InvitationController::class, 'show'])->name('invitation.accept');
    Route::post('/invitation/accept', [InvitationController::class, 'accept'])->name('invitation.accept.store');
});

Route::middleware(['auth', 'client'])->group(function () {
    Route::get('/portal', function () {
        return Inertia::render('Portal/Index');
    })->name('portal');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
