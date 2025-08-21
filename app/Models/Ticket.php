<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\TicketCycle;
use App\Models\Review;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'queue_id',
        'code_ticket',
        'number',
        'status',
        'cycle',
        'wants_notifications',
        'notification_channel',
        'called_at',
        'served_at',
        'session_id',
        'handled_by',
        'handled_at',
    ];
    
    protected $appends = ['position', 'estimated_wait_time', 'actual_wait_time', 'processing_time', 'is_being_handled', 'has_review'];

    protected $casts = [
        'wants_notifications' => 'boolean',
        'called_at' => 'datetime',
        'served_at' => 'datetime',
        'handled_at' => 'datetime',
    ];

    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }

    /**
     * Relation avec l'avis du ticket.
     */
    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    /**
     * Vérifie si le ticket a un avis.
     */
    public function getHasReviewAttribute(): bool
    {
        if (!array_key_exists('review', $this->relations)) {
            $this->load('review');
        }
        return !is_null($this->review) && $this->review->submitted_at !== null;
    }
    
    /**
     * Crée un nouvel avis pour ce ticket.
     */
    public function createReview(): Review
    {
        // Vérifier s'il existe déjà un avis pour ce ticket
        if ($this->review) {
            return $this->review;
        }

        // Créer un nouvel avis avec un token unique
        return $this->review()->create([
            'token' => Str::uuid(),
        ]);
    }
    
    /**
     * Crée automatiquement un avis lors du traitement d'un ticket
     * Cette méthode est appelée après la mise à jour du statut du ticket
     */
    protected static function booted()
    {
        static::updated(function ($ticket) {
            // Vérifier si le ticket vient d'être marqué comme traité
            if ($ticket->isDirty('status') && $ticket->status === 'served') {
                // Créer un avis pour ce ticket
                $ticket->createReview();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
    
    /**
     * L'utilisateur qui gère actuellement ce ticket
     */
    public function handler()
    {
        return $this->belongsTo(\App\Models\User::class, 'handled_by');
    }

    /**
     * Scope pour les tickets du cycle actuel
     */
    public function scopeCurrentCycle($query)
    {
        return $query->where('cycle', TicketCycle::currentCycle());
    }

    /**
     * Récupère la position du ticket dans la file d'attente
     */
    public function getPositionAttribute()
    {
        if (!in_array($this->status, ['waiting', 'paused'])) {
            return null;
        }

        return $this->queue->tickets()
            ->where('cycle', $this->cycle)
            ->whereIn('status', ['waiting', 'paused'])
            ->where('created_at', '<', $this->created_at)
            ->count() + 1;
    }

    /**
     * Calcule le temps d'attente estimé en secondes
     */
    public function getEstimatedWaitTimeAttribute()
    {
        if ($this->status !== 'waiting') {
            return null;
        }

        $ticketsBefore = $this->queue->tickets()
            ->where('status', 'waiting')
            ->where('created_at', '<', $this->created_at)
            ->count();

        $avgProcessingTime = $this->queue->tickets()
            ->where('status', 'served')
            ->whereNotNull('handled_at')
            ->whereNotNull('served_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, handled_at, served_at)) as avg_time')
            ->value('avg_time') ?: 60; // 60 secondes par défaut

        return $ticketsBefore * $avgProcessingTime;
    }

    /**
     * Calcule le temps d'attente réel en secondes
     */
    public function getActualWaitTimeAttribute()
    {
        if ($this->status === 'waiting') {
            return now()->diffInSeconds($this->created_at);
        }

        if ($this->status === 'in_progress' && $this->handled_at) {
            return $this->handled_at->diffInSeconds($this->created_at);
        }

        if (in_array($this->status, ['served', 'skipped']) && $this->handled_at) {
            return $this->handled_at->diffInSeconds($this->created_at);
        }

        return null;
    }

    /**
     * Calcule le temps de traitement en secondes
     */
    public function getProcessingTimeAttribute()
    {
        if ($this->status === 'served' && $this->handled_at && $this->served_at) {
            return $this->served_at->diffInSeconds($this->handled_at);
        }
        
        if ($this->status === 'in_progress' && $this->handled_at) {
            return now()->diffInSeconds($this->handled_at);
        }
        
        return null;
    }

    /**
     * Scope pour les tickets en attente
     */
    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    /**
     * Scope pour les tickets appelés (obsolète, à utiliser en_progress à la place)
     */
    public function scopeCalled($query)
    {
        return $query->where('status', 'called');
    }
    
    /**
     * Scope pour les tickets en cours de traitement par un agent
     */
    public function scopeInProgress($query, ?User $user = null)
    {
        $query = $query->where('status', 'in_progress');
        
        if ($user) {
            $query->where('handled_by', $user->id);
        }
        
        return $query;
    }

    /**
     * Scope pour les tickets traités
     */
    public function scopeServed($query)
    {
        return $query->where('status', 'served');
    }

    /**
     * Scope pour les tickets ignorés
     */
    public function scopeSkipped($query)
    {
        return $query->where('status', 'skipped');
    }
    
    /**
     * Vérifie si le ticket est en cours de traitement par un agent
     */
    public function getIsBeingHandledAttribute(): bool
    {
        return $this->status === 'in_progress' && $this->handled_by !== null;
    }
    
    /**
     * Vérifie si le ticket est en cours de traitement par un agent spécifique
     */
    public function isHandledBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        
        return $this->is_being_handled && $this->handled_by === $user->id;
    }
    
    /**
     * Attribue le ticket à un agent
     */
    public function assignTo(User $user): bool
    {
        return $this->update([
            'status' => 'in_progress',
            'handled_by' => $user->id,
            'handled_at' => now()
        ]);
    }
    
    /**
     * Libère le ticket (remet en attente)
     */
    public function release(): bool
    {
        return $this->update([
            'status' => 'waiting',
            'handled_by' => null,
            'handled_at' => null
        ]);
    }
    
    /**
     * Marque le ticket comme traité
     */
    public function markAsServed(): bool
    {
        return $this->update([
            'status' => 'served',
            'served_at' => now()
        ]);
    }
    
    /**
     * Marque le ticket comme ignoré
     */
    public function markAsSkipped(): bool
    {
        return $this->update([
            'status' => 'skipped',
            'handled_by' => null,
            'handled_at' => null
        ]);
    }
}
