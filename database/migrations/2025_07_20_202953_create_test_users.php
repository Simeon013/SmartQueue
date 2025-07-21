<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Exécute les migrations.
     */
    public function up(): void
    {
        // Créer un super administrateur de test
        User::create([
            'name' => 'Super Admin Test',
            'email' => 'superadmin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => UserRole::SUPER_ADMIN->value,
        ]);

        // Créer un administrateur de test
        User::create([
            'name' => 'Admin Test',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => UserRole::ADMIN->value,
        ]);

        // Créer un agent de test
        User::create([
            'name' => 'Agent Test',
            'email' => 'agent@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => UserRole::AGENT->value,
        ]);
    }

    /**
     * Annule les migrations.
     */
    public function down(): void
    {
        // Supprimer les utilisateurs de test
        User::whereIn('email', [
            'superadmin@example.com',
            'admin@example.com',
            'agent@example.com'
        ])->delete();
    }
};
