<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'establishment_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($queue) {
            if (empty($queue->code)) {
                do {
                    $code = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
                } while (self::where('code', $code)->exists());
                $queue->code = $code;
            }
        });
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function events()
    {
        return $this->hasMany(QueueEvent::class);
    }

    public function activeTickets()
    {
        return $this->tickets()->whereIn('status', ['waiting', 'called']);
    }

    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }
}