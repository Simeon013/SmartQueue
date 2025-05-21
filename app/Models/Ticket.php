<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_id',
        'number',
        'status',
        'session_id',
        'called_at',
        'served_at',
    ];

    protected $casts = [
        'called_at' => 'datetime',
        'served_at' => 'datetime',
    ];

    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }

    public function events()
    {
        return $this->hasMany(QueueEvent::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
