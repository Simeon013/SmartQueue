<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\QueueController;
use App\Http\Controllers\Admin\TicketController;
use App\Livewire\Admin\QueueTickets;
use App\Livewire\Admin\QueueTicketsHistory;
use App\Http\Controllers\Admin\EstablishmentController;
use App\Http\Controllers\Admin\SettingsController;

Route::get('/', function () {
    return view('public.queues.index');
})->name('public.queues.index');

Route::post('/find-queue', [App\Http\Controllers\Public\QueueController::class, 'find'])->name('public.queue.find');

// Routes d'affichage des files d'attente
// Mettre la route par code avant la route par ID
Route::get('/q/{code}', [App\Http\Controllers\Public\QueueController::class, 'showByCode'])->name('public.queue.show.code');

// Route pour l'affichage du QR code
Route::get('/qrcode/{code}', [App\Http\Controllers\Public\QrCodeController::class, 'show'])->name('public.qrcode.show');
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
// Route::middleware(['auth', 'role:agent'])->prefix('agent')->name('agent.')->group(function () {
//     Route::get('/queues/{queue}', [App\Http\Controllers\Agent\QueueController::class, 'dashboard'])->name('queue.dashboard');
//     Route::get('/queues/{queue}/tickets', [App\Http\Controllers\Agent\QueueController::class, 'getTickets'])->name('queue.tickets');
//     Route::post('/queues/{queue}/next', [App\Http\Controllers\Agent\QueueController::class, 'callNext'])->name('queue.next');
//     Route::post('/queues/{queue}/tickets/{ticket}/present', [App\Http\Controllers\Agent\QueueController::class, 'markPresent'])->name('queue.present');
//     Route::post('/queues/{queue}/tickets/{ticket}/skip', [App\Http\Controllers\Agent\QueueController::class, 'skip'])->name('queue.skip');
// });

// Accessible à tous les utilisateurs authentifiés
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('queues', \App\Http\Controllers\Admin\QueueController::class);
});

// Les autres routes admin restent protégées
// Routes administratives globales (nécessitent d'être admin)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

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
    Route::resource('establishments', EstablishmentController::class)->except(['show']);

    // Route pour la page paramètres générale
    Route::get('settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('settings/auto-close', [SettingsController::class, 'updateAutoClose'])->name('settings.auto-close');

    // Routes pour la gestion des utilisateurs
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::get('users/{user}/permissions', [\App\Http\Controllers\Admin\UserController::class, 'permissions'])->name('users.permissions');
    Route::post('users/{user}/assign-role', [\App\Http\Controllers\Admin\UserController::class, 'assignRole'])->name('users.assign-role');
    Route::post('users/{user}/remove-role', [\App\Http\Controllers\Admin\UserController::class, 'removeRole'])->name('users.remove-role');

    // Routes pour la gestion des rôles (statiques)
    Route::get('roles', [\App\Http\Controllers\Admin\RoleController::class, 'index'])->name('roles.index');
    Route::get('roles/{roleSlug}', [\App\Http\Controllers\Admin\RoleController::class, 'show'])->name('roles.show');
    Route::get('roles/{roleSlug}/users', [\App\Http\Controllers\Admin\RoleController::class, 'users'])->name('roles.users');
    
    // Désactiver les routes non utilisées pour la gestion statique des rôles
    Route::get('roles/create', [\App\Http\Controllers\Admin\RoleController::class, 'create'])->name('roles.create');
    Route::post('roles', [\App\Http\Controllers\Admin\RoleController::class, 'store'])->name('roles.store');
    Route::get('roles/{roleSlug}/edit', [\App\Http\Controllers\Admin\RoleController::class, 'edit'])->name('roles.edit');
    Route::put('roles/{roleSlug}', [\App\Http\Controllers\Admin\RoleController::class, 'update'])->name('roles.update');
    Route::delete('roles/{roleSlug}', [\App\Http\Controllers\Admin\RoleController::class, 'destroy'])->name('roles.destroy');
    
    // Désactiver les routes de gestion des permissions (gérées en dur dans l'énumération)
    Route::post('roles/{roleSlug}/assign-permission', [\App\Http\Controllers\Admin\RoleController::class, 'assignPermission'])->name('roles.assign-permission');
    Route::post('roles/{roleSlug}/remove-permission', [\App\Http\Controllers\Admin\RoleController::class, 'removePermission'])->name('roles.remove-permission');

    // Routes pour la gestion des permissions des files
    // (déplacées en dehors du groupe admin pour permettre l'accès aux agents avec les bonnes permissions)
    
    // Route pour voir les files d'un utilisateur (nécessite des droits admin)
    Route::get('users/{user}/queues', [\App\Http\Controllers\Admin\QueuePermissionController::class, 'userQueues'])->name('users.queues');
});

require __DIR__.'/auth.php';

// Routes pour la gestion des permissions des files (accessibles aux agents avec les bonnes permissions)
// Routes pour les actions rapides sur les files d'attente
Route::middleware(['auth', \App\Http\Middleware\CheckQueuePermission::class . ':manage'])->prefix('admin/queues')->name('admin.queues.')->group(function () {
    // Affichage du QR code en grand format
    Route::get('{queue}/qrcode', [\App\Http\Controllers\Admin\QueueController::class, 'showQrCode'])->name('qrcode');
    
    // Actions rapides
    Route::patch('{queue}/toggle-status', [\App\Http\Controllers\Admin\QueueController::class, 'toggleStatus'])->name('toggle-status');
    Route::patch('{queue}/toggle-pause', [\App\Http\Controllers\Admin\QueueController::class, 'togglePause'])->name('toggle-pause');
    Route::patch('{queue}/close', [\App\Http\Controllers\Admin\QueueController::class, 'close'])->name('close');
    
    // Gestion des permissions
    Route::get('{queue}/permissions', [\App\Http\Controllers\Admin\QueuePermissionController::class, 'index'])->name('permissions');
    Route::post('{queue}/permissions/mode', [\App\Http\Controllers\Admin\QueuePermissionController::class, 'setMode'])->name('permissions.mode');
    Route::post('{queue}/permissions/add-agents', [\App\Http\Controllers\Admin\QueuePermissionController::class, 'addSelectedAgents'])->name('permissions.add-agents');
    Route::patch('{queue}/permissions/{permission}/update', [\App\Http\Controllers\Admin\QueuePermissionController::class, 'updatePermission'])->name('permissions.update');
    Route::delete('{queue}/permissions/{permission}', [\App\Http\Controllers\Admin\QueuePermissionController::class, 'destroy'])->name('permissions.destroy');
    Route::get('{queue}/users', [\App\Http\Controllers\Admin\QueuePermissionController::class, 'queueUsers'])->name('users');
    Route::get('{queue}/search-users', [\App\Http\Controllers\Admin\QueuePermissionController::class, 'searchUsers'])->name('search-users');
    Route::delete('{queue}/permissions', [\App\Http\Controllers\Admin\QueuePermissionController::class, 'clearAll'])->name('permissions.clear');
    Route::post('{queue}/permissions/add-all-agents', [\App\Http\Controllers\Admin\QueuePermissionController::class, 'addAllAgents'])->name('permissions.add-all-agents');
});
