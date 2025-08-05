<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create 5 random establishments
        \App\Models\Establishment::factory(1)->create();

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Créer les permissions d'abord
        $this->call(PermissionsSeeder::class);

        // Puis créer les rôles avec leurs permissions
        // $this->call(RolesSeeder::class);

        // Créer un utilisateur admin par défaut
        \App\Models\User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
            'role' => UserRole::SUPER_ADMIN,
        ]);

        // Assigner le rôle admin à l'utilisateur admin
        $adminUser = \App\Models\User::where('email', 'admin@admin.com')->first();
        $adminRole = \App\Models\Role::where('slug', 'admin')->first();

        if ($adminUser && $adminRole) {
            $adminUser->assignRole($adminRole);
        }

        // Créer le service par défaut
        $this->call(DefaultServiceSeeder::class);
    }
}
