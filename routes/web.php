<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Routes publiques
Route::get('/q/{queue}', [App\Http\Controllers\Public\QueueController::class, 'show'])->name('public.queue.show');
Route::post('/q/{queue}/join', [App\Http\Controllers\Public\QueueController::class, 'join'])->name('public.queue.join');
Route::get('/q/{queue}/status', [App\Http\Controllers\Public\QueueController::class, 'status'])->name('public.queue.status');
Route::get('/q/{code}', [App\Http\Controllers\Public\QueueController::class, 'showByCode'])->name('public.queue.show.code');

// Routes agent
Route::middleware(['auth', 'role:agent'])->prefix('agent')->name('agent.')->group(function () {
    Route::get('/queues/{queue}', [App\Http\Controllers\Agent\QueueController::class, 'dashboard'])->name('queue.dashboard');
    Route::get('/queues/{queue}/tickets', [App\Http\Controllers\Agent\QueueController::class, 'getTickets'])->name('queue.tickets');
    Route::post('/queues/{queue}/next', [App\Http\Controllers\Agent\QueueController::class, 'callNext'])->name('queue.next');
    Route::post('/queues/{queue}/tickets/{ticket}/present', [App\Http\Controllers\Agent\QueueController::class, 'markPresent'])->name('queue.present');
    Route::post('/queues/{queue}/tickets/{ticket}/skip', [App\Http\Controllers\Agent\QueueController::class, 'skip'])->name('queue.skip');
});

// Routes admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');
    Route::resource('queues', App\Http\Controllers\Admin\QueueController::class);
});

require __DIR__.'/auth.php';