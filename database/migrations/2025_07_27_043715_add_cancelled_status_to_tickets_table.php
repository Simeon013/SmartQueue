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
        // Modifier la colonne status pour inclure le nouveau statut 'cancelled'
        Schema::table('tickets', function (Blueprint $table) {
            DB::statement("ALTER TABLE tickets MODIFY status ENUM('waiting', 'paused', 'in_progress', 'served', 'skipped', 'cancelled') NOT NULL DEFAULT 'waiting'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir à l'énumération précédente (sans 'cancelled')
        Schema::table('tickets', function (Blueprint $table) {
            // Mettre à jour les tickets annulés vers un autre statut avant de modifier la colonne
            DB::table('tickets')
                ->where('status', 'cancelled')
                ->update(['status' => 'skipped']);
                
            // Modifier la colonne pour enlever le statut 'cancelled'
            DB::statement("ALTER TABLE tickets MODIFY status ENUM('waiting', 'paused', 'in_progress', 'served', 'skipped') NOT NULL DEFAULT 'waiting'");
        });
    }
};
