<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Establishment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'description',
        'is_active',
        'type'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function queues()
    {
        return $this->hasMany(Queue::class);
    }

    public function admins()
    {
        return $this->belongsToMany(User::class, 'establishment_user')
            ->where('role', 'admin')
            ->withTimestamps();
    }
}
