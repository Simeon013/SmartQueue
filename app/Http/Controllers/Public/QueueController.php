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
        // Adapter la validation : plus de name, email, phone
        $validatedData = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $sessionId = $request->session()->getId();
        $existingTicket = $queue->tickets()
            ->where('session_id', $sessionId)
            ->whereIn('status', ['waiting', 'called'])
            ->first();

        if ($existingTicket) {
            return redirect()->route('public.queue.show.code', ['code' => $queue->code])
                ->with('error', 'Vous avez déjà un ticket en cours pour cette file.');
        }

        $lastTicket = $queue->tickets()->orderByDesc('id')->first();
        $codeTicket = !$lastTicket ? 'A-01' : (function() use ($lastTicket) {
            if (preg_match('/^([A-Z])-(\d+)$/', $lastTicket->code_ticket, $matches)) {
                $letter = $matches[1];
                $number = (int)$matches[2];
                if ($number >= 99) {
                    $letter = chr(ord($letter) + 1);
                    $number = 1;
                } else {
                    $number++;
                }
                return sprintf('%s-%02d', $letter, $number);
            }
            return 'A-01';
        })();

        $ticket = $queue->tickets()->create([
            'queue_id' => $queue->id,
            'code_ticket' => $codeTicket,
            'status' => 'waiting',
            'session_id' => $sessionId,
            'notes' => $validatedData['notes'] ?? null,
        ]);

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
