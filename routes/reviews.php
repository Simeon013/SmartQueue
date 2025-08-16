<?php

use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

// Routes accessibles par les visiteurs (sans authentification)
Route::prefix('reviews')->name('reviews.')->group(function () {
    // Afficher le formulaire d'avis
    Route::get('/{token}', [ReviewController::class, 'showForm'])->name('show');

    // Soumettre un avis
    Route::post('/{token}', [ReviewController::class, 'submit'])->name('submit');

    // Page de remerciement
    Route::get('/thank-you', [ReviewController::class, 'thankYou'])->name('thank-you');
});

// Routes protégées pour l'administration
Route::middleware(['auth', 'can:viewAny,App\\Models\\Review'])->prefix('admin/reviews')->name('admin.reviews.')->group(function () {
    // Liste des avis
    Route::get('/', [ReviewController::class, 'index'])->name('index');

    // Statistiques des avis
    Route::get('/stats', [ReviewController::class, 'stats'])->name('stats');
});
