<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\QueueController;
use App\Http\Controllers\Admin\TicketController;
use App\Livewire\Admin\QueueTickets;
use App\Livewire\Admin\QueueTicketsHistory;
use App\Http\Controllers\Admin\EstablishmentController;

Route::get('/', function () {
    return view('public.queues.index');
})->name('public.queues.index');

Route::post('/find-queue', [App\Http\Controllers\Public\QueueController::class, 'find'])->name('public.queue.find');

// Routes d'affichage des files d'attente
// Mettre la route par code avant la route par ID
Route::get('/q/{code}', [App\Http\Controllers\Public\QueueController::class, 'showByCode'])->name('public.queue.show.code');
Route::get('/q/{queue}', [App\Http\Controllers\Public\QueueController::class, 'show'])->name('public.queue.show');

Route::get('/q/{queue_code}/ticket/{ticket_code}', [App\Http\Controllers\Public\QueueController::class, 'ticketStatus'])->name('public.ticket.status');
Route::delete('/q/{ticket}/cancel', [App\Http\Controllers\Public\QueueController::class, 'cancelTicket'])->name('public.ticket.cancel');
Route::post('/q/{ticket}/pause', [App\Http\Controllers\Public\QueueController::class, 'pauseTicket'])->name('public.ticket.pause');
Route::post('/q/{ticket}/resume', [App\Http\Controllers\Public\QueueController::class, 'resumeTicket'])->name('public.ticket.resume');

Route::post('/q/{queue}/join', [App\Http\Controllers\Public\QueueController::class, 'join'])->name('public.queue.join');


Route::get('/dashboard', function () {
    return view('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

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

    Route::resource('queues', QueueController::class);

    // Routes pour les tickets dans les files d'attente
    Route::prefix('queues/{queue}/tickets')->name('queues.tickets.')->group(function () {
        Route::get('/', QueueTickets::class)->name('index');
        Route::get('/history', QueueTicketsHistory::class)->name('history');
        Route::post('/', [QueueTickets::class, 'store'])->name('store');
        Route::delete('/{ticket}', [QueueTickets::class, 'destroy'])->name('destroy');
        Route::patch('/{ticket}/status', [QueueTickets::class, 'updateStatus'])->name('status');
        Route::put('/{ticket}', [QueueTickets::class, 'update'])->name('update');
    });

    // Routes pour la gestion des établissements
    Route::get('settings/establishment', [EstablishmentController::class, 'settings'])->name('establishment.settings');
    Route::put('settings/establishment', [EstablishmentController::class, 'updateSettings'])->name('establishment.settings.update');
});

require __DIR__.'/auth.php';
