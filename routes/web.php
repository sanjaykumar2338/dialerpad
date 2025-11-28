<?php

use App\Http\Controllers\DialerController;
use App\Http\Controllers\EsimController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\CallSessionExportController;
use App\Http\Controllers\Admin\EsimCodeController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

Route::prefix('c')->group(function () {
    Route::get('{uuid}', [DialerController::class, 'show'])->name('dialer.show');
    Route::post('{uuid}/start-call', [DialerController::class, 'startCall'])->name('dialer.start');
    Route::post('{uuid}/end-call', [DialerController::class, 'endCall'])->name('dialer.end');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('call-cards', \App\Http\Controllers\Admin\CallCardController::class);
    Route::get('call-cards-export', [\App\Http\Controllers\Admin\CallCardExportController::class, 'exportZip'])->name('call-cards.export');
    Route::get('call-sessions', [\App\Http\Controllers\Admin\CallSessionController::class, 'index'])->name('call-sessions.index');
    Route::get('call-sessions/export', [CallSessionExportController::class, 'export'])->name('call-sessions.export');
    Route::resource('esim-types', \App\Http\Controllers\Admin\EsimTypeController::class);
    Route::resource('esim-requests', \App\Http\Controllers\Admin\EsimRequestController::class)->only(['index', 'edit', 'update', 'destroy']);
    Route::resource('esim-codes', EsimCodeController::class);
});

Route::get('/esim/activate', fn () => redirect('/'))->name('esim.activate.redirect');
Route::get('/esim/{uuid}', [EsimController::class, 'showForm'])->name('esim.form');
Route::post('/esim/{uuid}', [EsimController::class, 'submit'])->name('esim.submit');

/* Breeze dashboard/profile if needed */
Route::get('/dashboard', fn () => view('dashboard'))->middleware(['auth', 'verified'])->name('dashboard');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
