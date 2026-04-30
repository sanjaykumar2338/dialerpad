<?php

use App\Http\Controllers\Account\BatchController as AccountBatchController;
use App\Http\Controllers\Account\BatchRequestController as AccountBatchRequestController;
use App\Http\Controllers\Account\DashboardController as AccountDashboardController;
use App\Http\Controllers\Account\ReportController as AccountReportController;
use App\Http\Controllers\Admin\BatchRequestController as AdminBatchRequestController;
use App\Http\Controllers\Admin\CallCardController;
use App\Http\Controllers\Admin\CallCardExportController;
use App\Http\Controllers\Admin\CallSessionExportController;
use App\Http\Controllers\Admin\DistributorController;
use App\Http\Controllers\Admin\DistributionBatchExportController;
use App\Http\Controllers\Admin\EsimCodeController;
use App\Http\Controllers\DialerController;
use App\Http\Controllers\EsimController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

Route::prefix('c')->group(function () {
    Route::get('{uuid}', [DialerController::class, 'show'])->name('dialer.show');
    Route::post('{uuid}/start-call', [DialerController::class, 'startCall'])->name('dialer.start');
    Route::post('{uuid}/end-call', [DialerController::class, 'endCall'])->name('dialer.end');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('batch-requests', [AdminBatchRequestController::class, 'index'])->name('batch-requests.index');
    Route::post('batch-requests/{batchRequest}/approve', [AdminBatchRequestController::class, 'approve'])->name('batch-requests.approve');
    Route::post('batch-requests/{batchRequest}/generate', [AdminBatchRequestController::class, 'generate'])->name('batch-requests.generate');
    Route::post('batch-requests/{batchRequest}/sent', [AdminBatchRequestController::class, 'markSent'])->name('batch-requests.sent');
    Route::post('batch-requests/{batchRequest}/document', [AdminBatchRequestController::class, 'uploadDocument'])->name('batch-requests.document');
    Route::post('batch-requests/{batchRequest}/complete', [AdminBatchRequestController::class, 'complete'])->name('batch-requests.complete');
    Route::get('distribution-batches/{batch}/download', [DistributionBatchExportController::class, 'download'])->name('distribution-batches.download');
    Route::patch('distributors/{distributor}/status', [DistributorController::class, 'updateStatus'])->name('distributors.status');
    Route::resource('distributors', DistributorController::class)->except(['show']);
    Route::resource('call-cards', CallCardController::class);
    Route::get('call-cards/batch/{batch}/start-download', [CallCardController::class, 'startBatchDownload'])->name('call-cards.batch-start');
    Route::get('call-cards/batch/{batch}/zip', [CallCardExportController::class, 'exportBatchZip'])->name('call-cards.batch-zip');
    Route::get('call-cards-export', [CallCardExportController::class, 'exportZip'])->name('call-cards.export');
    Route::get('call-sessions', [\App\Http\Controllers\Admin\CallSessionController::class, 'index'])->name('call-sessions.index');
    Route::get('call-sessions/export', [CallSessionExportController::class, 'export'])->name('call-sessions.export');
    Route::resource('esim-types', \App\Http\Controllers\Admin\EsimTypeController::class);
    Route::resource('esim-requests', \App\Http\Controllers\Admin\EsimRequestController::class)->only(['index', 'edit', 'update', 'destroy']);
    Route::resource('esim-codes', EsimCodeController::class);
});

Route::get('/esim/activate', fn () => redirect('/'))->name('esim.activate.redirect');
Route::get('/esim/{uuid}', [EsimController::class, 'showForm'])->name('esim.form');
Route::post('/esim/{uuid}', [EsimController::class, 'submit'])->name('esim.submit');

Route::middleware(['auth', 'verified', 'distributor'])->group(function () {
    Route::get('/dashboard', [AccountDashboardController::class, 'index'])->name('dashboard');
    Route::get('/request-cards', [AccountBatchRequestController::class, 'create'])->name('account.requests.create');
    Route::post('/request-cards', [AccountBatchRequestController::class, 'store'])->name('account.requests.store');
    Route::get('/batches', [AccountBatchController::class, 'index'])->name('account.batches.short');
    Route::get('/my-batches', [AccountBatchController::class, 'index'])->name('account.batches.index');
    Route::get('/reports', [AccountReportController::class, 'index'])->name('account.reports.index');
    Route::get('/settings', [ProfileController::class, 'edit'])->name('account.settings.edit');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
