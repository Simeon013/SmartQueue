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
        Schema::table('tickets', function (Blueprint $table) {
            // Ajouter le statut 'cancelled' aux valeurs possibles
            DB::statement("ALTER TABLE tickets MODIFY status ENUM('waiting', 'called', 'served', 'skipped', 'cancelled') NOT NULL DEFAULT 'waiting'");
            
            // Ajouter les colonnes pour l'annulation
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('cancellation_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Retirer les colonnes ajoutées
            $table->dropColumn(['cancelled_at', 'cancelled_by', 'cancellation_reason']);
            
            // Remettre l'énumération à son état d'origine
            DB::statement("ALTER TABLE tickets MODIFY status ENUM('waiting', 'called', 'served', 'skipped') NOT NULL DEFAULT 'waiting'");
        });
    }
};
