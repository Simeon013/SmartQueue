<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_id',
        'code_ticket',
        'number',
        'status',
        'wants_notifications',
        'notification_channel',
        'called_at',
        'served_at',
        'session_id',
    ];

    protected $casts = [
        'wants_notifications' => 'boolean',
        'called_at' => 'datetime',
        'served_at' => 'datetime',
    ];

    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }

    public function getPositionAttribute()
    {
        if (!in_array($this->status, ['waiting', 'paused'])) {
            return null;
        }

        return $this->queue->tickets()
            ->whereIn('status', ['waiting', 'paused'])
            ->where('created_at', '<', $this->created_at)
            ->count() + 1;
    }

    public function getEstimatedWaitTimeAttribute()
    {
        if (!$this->position) {
            return null;
        }

        // Calculer le temps d'attente moyen par personne
        $averageWaitTime = $this->queue->tickets()
            ->whereNotNull('served_at')
            ->whereNotNull('called_at')
            ->avg(DB::raw('TIMESTAMPDIFF(SECOND, called_at, served_at)'));

        if (!$averageWaitTime) {
            return null;
        }

        // Estimer le temps d'attente en fonction de la position
        $estimatedTime = round(($averageWaitTime * $this->position) / 60, 1); // en minutes

        return $estimatedTime < 1 ? '-1min' : $estimatedTime . 'min';
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeCalled($query)
    {
        return $query->where('status', 'called');
    }

    public function scopeServed($query)
    {
        return $query->where('status', 'served');
    }

    public function scopeSkipped($query)
    {
        return $query->where('status', 'skipped');
    }
}
