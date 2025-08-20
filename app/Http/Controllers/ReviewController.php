<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReviewController extends Controller
{
    /**
     * Affiche le formulaire d'avis.
     */
    public function showForm(string $token)
    {
        $review = Review::where('token', $token)->firstOrFail();

        // Vérifier si l'avis a déjà été soumis
        if ($review->isSubmitted()) {
            return view('reviews.already-submitted');
        }

        return view('reviews.form', [
            'review' => $review,
            'ticket' => $review->ticket,
        ]);
    }

    /**
     * Traite la soumission du formulaire d'avis.
     * Cette méthode est maintenant gérée directement par le composant Livewire.
     * Elle est conservée pour la rétrocompatibilité.
     */
    public function submit(Request $request, string $token)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review = Review::where('token', $token)->firstOrFail();

        // Vérifier si l'avis a déjà été soumis
        if ($review->isSubmitted()) {
            return redirect()->route('reviews.thank-you');
        }

        // Mettre à jour l'avis
        $review->update([
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'submitted_at' => now(),
        ]);

        return redirect()->route('reviews.thank-you');
    }

    /**
     * Affiche la page de remerciement.
     */
    public function thankYou()
    {
        return view('reviews.thank-you');
    }

    /**
     * Affiche la liste des avis (admin).
     */
    public function index()
    {
        Gate::authorize('viewAny', Review::class);

        $reviews = Review::with(['ticket.handler'])
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->paginate(15);

        return view('admin.reviews.index', compact('reviews'));
    }

    /**
     * Affiche les statistiques des avis (admin).
     */
    public function stats()
    {
        Gate::authorize('viewStatistics', Review::class);

        $stats = [
            'total_reviews' => Review::whereNotNull('submitted_at')->count(),
            'average_rating' => Review::whereNotNull('submitted_at')->avg('rating') ?? 0,
            'ratings_distribution' => Review::whereNotNull('submitted_at')
                ->selectRaw('rating, COUNT(*) as count')
                ->groupBy('rating')
                ->orderBy('rating', 'desc')
                ->pluck('count', 'rating')
                ->toArray(),
        ];

        // S'assurer que toutes les notes de 1 à 5 sont présentes dans la distribution
        for ($i = 1; $i <= 5; $i++) {
            if (!isset($stats['ratings_distribution'][$i])) {
                $stats['ratings_distribution'][$i] = 0;
            }
        }

        // Trier par note décroissante
        krsort($stats['ratings_distribution']);

        return view('admin.reviews.stats', [
            'stats' => $stats,
        ]);
    }
}
