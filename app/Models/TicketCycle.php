<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketCycle extends Model
{
    protected $fillable = ['current_cycle', 'last_reset_at'];
    protected $casts = ['last_reset_at' => 'datetime'];
    protected $table = 'ticket_cycles';

    /**
     * Récupère le cycle actuel
     */
    public static function currentCycle(): int
    {
        return (int) static::firstOrCreate([], ['current_cycle' => 1])->current_cycle;
    }

    /**
     * Passe au cycle suivant et retourne le nouveau numéro de cycle
     */
    public static function nextCycle(): int
    {
        $cycle = static::firstOrCreate([], ['current_cycle' => 1]);
        $cycle->increment('current_cycle');
        $cycle->update(['last_reset_at' => now()]);
        
        return $cycle->current_cycle;
    }
}
