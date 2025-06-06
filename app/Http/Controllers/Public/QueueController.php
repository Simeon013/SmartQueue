<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class QueueController extends Controller
{
    public function show(Queue $queue)
    {
        return view('public.queue.show', compact('queue'));
    }

    public function join(Request $request, Queue $queue)
    {
        // Valider les données du formulaire (nom est requis, email, phone, notes sont optionnels)
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        // Vérifier si le client a déjà un ticket actif dans cette file en utilisant l'ID de session
        $sessionId = $request->session()->getId();
        Log::info('Public Queue Join: Session ID', ['session_id' => $sessionId]); // Log the session ID

        $existingTicket = $queue->tickets()
            ->where('session_id', $sessionId)
            ->whereIn('status', ['waiting', 'called'])
            ->first();

        if ($existingTicket) {
            // Si un ticket actif existe déjà pour cette session dans cette file,
            // rediriger le client vers la page de la file en utilisant le code de la file
            return redirect()->route('public.queue.show.code', ['code' => $queue->code])
                ->with('error', 'Vous avez déjà un ticket en cours pour cette file.');
        }

        // --- Logique de génération du code_ticket (basée sur la séquence Lettre-Numéro A-01, A-02, etc.) ---
        // Cette logique est similaire à celle utilisée dans l'administration.
        // Récupérer le dernier ticket créé *spécifiquement pour cette file* pour déterminer le prochain code.
        // On utilise orderByDesc('id') pour s'assurer de prendre le ticket créé le plus récemment.
        $lastTicket = $queue->tickets()->orderByDesc('id')->first();

        if (!$lastTicket) {
            // Si c'est le tout premier ticket de cette file, commencer la séquence à A-01.
            $codeTicket = 'A-01';
        } else {
            // Si des tickets existent déjà, extraire la lettre et le numéro du code_ticket du dernier ticket.
            // Le format attendu est "LETTRE-NUMERO" (ex: "A-05", "B-12").
            if (preg_match('/^([A-Z])-(\d+)$/', $lastTicket->code_ticket, $matches)) {
                $letter = $matches[1]; // Récupère la lettre (ex: "A")
                $number = (int)$matches[2]; // Récupère le numéro (ex: 5, converti en entier)

                // Si le numéro atteint 99, on passe à la lettre suivante et on réinitialise le numéro à 1.
                if ($number >= 99) {
                    // Calcule le caractère ASCII de la lettre suivante et le convertit en lettre (ex: 'A' + 1 = 'B').
                    $letter = chr(ord($letter) + 1);
                    $number = 1; // Le numéro recommence à 1 pour la nouvelle lettre.
                } else {
                    // Sinon, on incrémente simplement le numéro du ticket.
                    $number++;
                }

                // Formate le nouveau code_ticket. sprintf('%s-%02d', ...) assure que le numéro a toujours deux chiffres (ex: 1 devient 01).
                $codeTicket = sprintf('%s-%02d', $letter, $number);
            } else {
                 // Cas de fallback si le code_ticket du dernier ticket ne correspond pas au format attendu (Lettre-Numéro).
                 // Cela peut indiquer un problème dans la génération précédente des codes.
                 // Pour éviter de bloquer, on loggue l'erreur pour pouvoir l'investiguer, et on recommence la séquence à A-01 pour cette file.
                 // Idéalement, tous les code_ticket pour une file devraient suivre ce format une fois cette logique en place.
                 Log::error('Format de code_ticket inattendu pour le dernier ticket de la file lors de la génération publique.', [
                     'queue_id' => $queue->id,
                     'last_code' => $lastTicket->code_ticket ?? 'aucun ticket trouvé pour la file'
                 ]);
                 $codeTicket = 'A-01'; // Recommencer la séquence pour cette file en cas d'erreur de format sur le dernier ticket.
            }
        }
        // --- Fin Logique de génération du code_ticket ---

        // Créer le nouveau ticket dans la base de données avec toutes les informations collectées et générées.
        $ticket = $queue->tickets()->create([
            'queue_id' => $queue->id, // Associe le ticket à la file d'attente actuelle.
            'code_ticket' => $codeTicket, // Le code unique du ticket généré (Lettre-Numéro).
            'status' => 'waiting', // Le statut initial du ticket est "waiting" (en attente).
            'session_id' => $sessionId, // Lie ce ticket à la session de navigateur actuelle du client.
            // Inclure les données saisies par le client dans le formulaire :
            'name' => $validatedData['name'], // Nom (requis)
            'email' => $validatedData['email'] ?? null, // Email (optionnel, utilise null si non fourni)
            'phone' => $validatedData['phone'] ?? null, // Téléphone (optionnel, utilise null si non fourni)
            'notes' => $validatedData['notes'] ?? null, // Notes (optionnel, utilise null si non fourni)
            // Remarque : Selon votre structure, le champ 'number' n'existe pas en base de données et n'est pas stocké séparément.
        ]);

        // Rediriger le client vers la page d'affichage de la file d'attente pour qu'il puisse voir son ticket nouvellement créé
        // et l'état actuel de la file. On utilise la route qui prend le code unique de la file.
        return redirect()->route('public.queue.show.code', ['code' => $queue->code])
            ->with('success', 'Votre ticket ' . $ticket->code_ticket . ' a été créé avec succès !');
    }

    public function status(Queue $queue)
    {
        $ticket = $queue->tickets()
            ->where('session_id', request()->session()->getId())
            ->whereIn('status', ['waiting', 'called'])
            ->first();

        return response()->json([
            'ticket' => $ticket,
            'queue_status' => $queue->status,
            'position' => $ticket ? $queue->tickets()
                ->where('status', 'waiting')
                ->where('id', '<', $ticket->id)
                ->count() + 1 : null,
        ]);
    }

    public function showByCode($code)
    {
        $queue = \App\Models\Queue::where('code', $code)->firstOrFail();
        return view('public.queue.show', compact('queue'));
    }

    public function find(Request $request)
    {
        $request->validate([
            'queue_code' => 'required|string|exists:queues,code',
        ]);

        $queueCode = $request->input('queue_code');

        return redirect()->route('public.queue.show.code', ['code' => $queueCode]);
    }
}