<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Créer les permissions d'abord
        $this->call(PermissionsSeeder::class);

        // Puis créer les rôles avec leurs permissions
        $this->call(RolesSeeder::class);

        // Créer un utilisateur admin par défaut
        \App\Models\User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@smartqueue.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Assigner le rôle admin à l'utilisateur admin
        $adminUser = \App\Models\User::where('email', 'admin@smartqueue.com')->first();
        $adminRole = \App\Models\Role::where('slug', 'admin')->first();

        if ($adminUser && $adminRole) {
            $adminUser->assignRole($adminRole);
        }
    }
}
