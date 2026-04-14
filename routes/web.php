<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\KnowledgeEntryController;
use App\Http\Controllers\KnowledgeLinkController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\NoteFolderController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect(auth()->user()->role === 'admin' ? '/dashboard' : '/portal');
    }
    return redirect()->route('login');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/invitations/create', [InvitationController::class, 'create'])->name('invitations.create');
    Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');

    Route::resource('clients', ClientController::class);
    Route::get('clients/{client}/portal-preview', [ClientController::class, 'portalPreview'])->name('clients.portal-preview');

    Route::resource('tasks', TaskController::class)->except(['show', 'create', 'edit']);
    Route::get('tasks/archived', [TaskController::class, 'archived'])->name('tasks.archived');
    Route::post('tasks/close-month', [TaskController::class, 'closeMonth'])->name('tasks.closeMonth');
    Route::put('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    Route::post('tasks/{task}/comments', [TaskCommentController::class, 'store'])->name('task-comments.store');
    Route::delete('task-comments/{comment}', [TaskCommentController::class, 'destroy'])->name('task-comments.destroy');

    Route::resource('billing', BillingController::class)->except(['show']);
    Route::post('billing/{billing}/afip-pdf', [BillingController::class, 'uploadAfipPdf'])->name('billing.afip-pdf');
    Route::get('billing/{billing}/afip-pdf/download', [BillingController::class, 'downloadAfipPdf'])->name('billing.afip-pdf.download');

    Route::resource('quotes', QuoteController::class)->except(['show']);
    Route::patch('quotes/{quote}/estado', [QuoteController::class, 'updateEstado'])->name('quotes.updateEstado');
    Route::get('quotes/{quote}/pdf', [QuoteController::class, 'pdf'])->name('quotes.pdf');

    // Notes — search before resource to avoid {note} capturing "search"
    Route::get('notes/search', [NoteController::class, 'index'])->name('notes.search');
    Route::resource('notes', NoteController::class);
    Route::post('note-folders', [NoteFolderController::class, 'store'])->name('note-folders.store');
    Route::patch('note-folders/{noteFolder}', [NoteFolderController::class, 'update'])->name('note-folders.update');
    Route::delete('note-folders/{noteFolder}', [NoteFolderController::class, 'destroy'])->name('note-folders.destroy');

    // Knowledge — search before resource to avoid {knowledge} capturing "search"
    Route::get('knowledge/search', [KnowledgeEntryController::class, 'index'])->name('knowledge.search');
    Route::resource('knowledge', KnowledgeEntryController::class);
    Route::post('knowledge/{knowledge}/links', [KnowledgeLinkController::class, 'store'])->name('knowledge-links.store');
    Route::delete('knowledge-links/{knowledgeLink}', [KnowledgeLinkController::class, 'destroy'])->name('knowledge-links.destroy');
});

// Public invitation acceptance (requires valid signature)
Route::middleware('signed')->group(function () {
    Route::get('/invitation/accept', [InvitationController::class, 'show'])->name('invitation.accept');
    Route::post('/invitation/accept', [InvitationController::class, 'accept'])->name('invitation.accept.store');
});

Route::middleware(['auth', 'client'])->group(function () {
    Route::get('/portal', [PortalController::class, 'index'])->name('portal');
    Route::get('/portal/tasks/{task}', [PortalController::class, 'showTask'])->name('portal.tasks.show');
    Route::get('/portal/billing/{billing}', [PortalController::class, 'showBilling'])->name('portal.billing.show');
    Route::get('/portal/billing/{billing}/afip-pdf', [PortalController::class, 'downloadAfipPdf'])->name('portal.billing.afip-pdf');
    Route::get('/portal/quotes/{quote}/pdf', [PortalController::class, 'pdf'])->name('portal.quotes.pdf');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
