<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\Finance\BankAccountController;
use App\Http\Controllers\Finance\FinanceDashboardController;
use App\Http\Controllers\Finance\FixedExpenseController;
use App\Http\Controllers\Finance\PayslipController;
use App\Http\Controllers\Finance\VariableExpenseController;
use App\Http\Controllers\KnowledgeEntryController;
use App\Http\Controllers\KnowledgeLinkController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\NoteFolderController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicTravelController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TravelAccommodationController;
use App\Http\Controllers\TravelActivityController;
use App\Http\Controllers\TravelController;
use App\Http\Controllers\TravelDocumentController;
use App\Http\Controllers\TravelSegmentController;
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

    Route::resource('billing', BillingController::class);
    Route::post('billing/{billing}/send-email', [BillingController::class, 'sendEmail'])->name('billing.send-email');
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

    // Personal — Finance
    Route::get('finance', [FinanceDashboardController::class, 'index'])->name('finance.dashboard');
    Route::get('finance/payslips', [PayslipController::class, 'index'])->name('finance.payslips.index');
    Route::get('finance/payslips/create', [PayslipController::class, 'create'])->name('finance.payslips.create');
    Route::post('finance/payslips/parse', [PayslipController::class, 'parse'])->name('finance.payslips.parse');
    Route::post('finance/payslips', [PayslipController::class, 'store'])->name('finance.payslips.store');
    Route::get('finance/payslips/{payslip}', [PayslipController::class, 'show'])->name('finance.payslips.show');
    Route::delete('finance/payslips/{payslip}', [PayslipController::class, 'destroy'])->name('finance.payslips.destroy');
    Route::get('finance/accounts', [BankAccountController::class, 'index'])->name('finance.accounts.index');
    Route::post('finance/accounts', [BankAccountController::class, 'store'])->name('finance.accounts.store');
    Route::patch('finance/accounts/{account}', [BankAccountController::class, 'update'])->name('finance.accounts.update');
    Route::delete('finance/accounts/{account}', [BankAccountController::class, 'destroy'])->name('finance.accounts.destroy');
    Route::post('finance/accounts/{account}/balances', [BankAccountController::class, 'addBalance'])->name('finance.accounts.balances.store');
    Route::get('finance/expenses', [FixedExpenseController::class, 'index'])->name('finance.expenses.index');
    Route::post('finance/fixed-expenses', [FixedExpenseController::class, 'store'])->name('finance.fixed-expenses.store');
    Route::patch('finance/fixed-expenses/{fixedExpense}', [FixedExpenseController::class, 'update'])->name('finance.fixed-expenses.update');
    Route::delete('finance/fixed-expenses/{fixedExpense}', [FixedExpenseController::class, 'destroy'])->name('finance.fixed-expenses.destroy');
    Route::post('finance/variable-expenses', [VariableExpenseController::class, 'store'])->name('finance.variable-expenses.store');
    Route::patch('finance/variable-expenses/{variableExpense}', [VariableExpenseController::class, 'update'])->name('finance.variable-expenses.update');
    Route::delete('finance/variable-expenses/{variableExpense}', [VariableExpenseController::class, 'destroy'])->name('finance.variable-expenses.destroy');

    // Personal — Travels
    Route::resource('travels', TravelController::class);
    Route::post('travels/{travel}/documents', [TravelDocumentController::class, 'store'])->name('travels.documents.store');
    Route::get('travels/{travel}/documents/{document}/download', [TravelDocumentController::class, 'download'])->name('travels.documents.download');
    Route::delete('travels/{travel}/documents/{document}', [TravelDocumentController::class, 'destroy'])->name('travels.documents.destroy');
    Route::get('travels/{travel}/segments/create', [TravelSegmentController::class, 'create'])->name('travels.segments.create');
    Route::post('travels/{travel}/segments', [TravelSegmentController::class, 'store'])->name('travels.segments.store');
    Route::get('travels/{travel}/segments/{segment}/edit', [TravelSegmentController::class, 'edit'])->name('travels.segments.edit');
    Route::put('travels/{travel}/segments/{segment}', [TravelSegmentController::class, 'update'])->name('travels.segments.update');
    Route::delete('travels/{travel}/segments/{segment}', [TravelSegmentController::class, 'destroy'])->name('travels.segments.destroy');
    Route::get('travels/{travel}/accommodations/create', [TravelAccommodationController::class, 'create'])->name('travels.accommodations.create');
    Route::post('travels/{travel}/accommodations', [TravelAccommodationController::class, 'store'])->name('travels.accommodations.store');
    Route::get('travels/{travel}/accommodations/{accommodation}/edit', [TravelAccommodationController::class, 'edit'])->name('travels.accommodations.edit');
    Route::put('travels/{travel}/accommodations/{accommodation}', [TravelAccommodationController::class, 'update'])->name('travels.accommodations.update');
    Route::delete('travels/{travel}/accommodations/{accommodation}', [TravelAccommodationController::class, 'destroy'])->name('travels.accommodations.destroy');
    Route::get('travels/{travel}/activities/create', [TravelActivityController::class, 'create'])->name('travels.activities.create');
    Route::post('travels/{travel}/activities', [TravelActivityController::class, 'store'])->name('travels.activities.store');
    Route::get('travels/{travel}/activities/{activity}/edit', [TravelActivityController::class, 'edit'])->name('travels.activities.edit');
    Route::put('travels/{travel}/activities/{activity}', [TravelActivityController::class, 'update'])->name('travels.activities.update');
    Route::delete('travels/{travel}/activities/{activity}', [TravelActivityController::class, 'destroy'])->name('travels.activities.destroy');
});

// Public travel share view (token-based, no auth)
Route::get('/v/{token}', [PublicTravelController::class, 'show'])->name('travels.public');

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
