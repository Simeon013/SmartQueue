<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // D'abord, ajouter les nouvelles colonnes
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('handled_by')
                  ->nullable()
                  ->after('status')
                  ->constrained('users')
                  ->onDelete('set null');
            $table->timestamp('handled_at')->nullable()->after('handled_by');
        });

        // Ensuite, mettre à jour les statuts existants si nécessaire
        // On convertit les statuts 'called' en 'in_progress' pour la nouvelle logique
        DB::table('tickets')
            ->where('status', 'called')
            ->update([
                'status' => 'in_progress',
                'handled_at' => now()
            ]);

        // Enfin, modifier la colonne status pour inclure le nouveau statut
        // Utilisation de DB::statement pour modifier la colonne enum
        // Note: Cette approche est spécifique à MySQL/MariaDB
        DB::statement("ALTER TABLE tickets 
            MODIFY COLUMN status 
            ENUM('waiting', 'called', 'in_progress', 'served', 'skipped') 
            NOT NULL DEFAULT 'waiting'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Avant de supprimer les colonnes, convertir les statuts 'in_progress' en 'called'
        DB::table('tickets')
            ->where('status', 'in_progress')
            ->update(['status' => 'called']);

        // Modifier la colonne status pour enlever le statut 'in_progress'
        DB::statement("ALTER TABLE tickets 
            MODIFY COLUMN status 
            ENUM('waiting', 'called', 'served', 'skipped') 
            NOT NULL DEFAULT 'waiting'");

        // Enfin, supprimer les colonnes ajoutées
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['handled_by']);
            $table->dropColumn(['handled_by', 'handled_at']);
        });
    }
};
