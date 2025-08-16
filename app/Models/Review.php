<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ticket_id',
        'rating',
        'comment',
        'token',
        'submitted_at',
    ];

    /**
     * Les attributs qui doivent être convertis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    /**
     * Relation avec le ticket associé.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Marquer l'avis comme soumis.
     */
    public function markAsSubmitted(): void
    {
        $this->update([
            'submitted_at' => now(),
        ]);
    }

    /**
     * Vérifier si l'avis a été soumis.
     */
    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    /**
     * Génére un nouveau token unique pour l'avis.
     */
    public static function generateToken(): string
    {
        do {
            $token = bin2hex(random_bytes(16));
        } while (self::where('token', $token)->exists());

        return $token;
    }
}
