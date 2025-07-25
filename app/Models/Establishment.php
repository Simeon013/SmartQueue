<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Establishment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'address',
        'city',
        'postal_code',
        'country',
        // 'is_active',
    ];

    // protected $casts = [
    //     'is_active' => 'boolean',
    // ];

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
