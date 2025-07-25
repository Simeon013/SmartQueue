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
        // Ajouter la nouvelle colonne status si elle n'existe pas déjà
        if (!Schema::hasColumn('queues', 'status')) {
            Schema::table('queues', function (Blueprint $table) {
                $table->enum('status', ['open', 'paused', 'blocked', 'closed'])->default('open')->after('code');
            });
        }

        // Mettre à jour les valeurs existantes
        \DB::table('queues')
            ->where('is_active', true)
            ->update(['status' => 'open']);
            
        \DB::table('queues')
            ->where('is_active', false)
            ->update(['status' => 'closed']);
            
        // Supprimer les anciennes colonnes si elles existent
        if (Schema::hasColumn('queues', 'is_active')) {
            Schema::table('queues', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
        
        if (Schema::hasColumn('queues', 'is_paused')) {
            Schema::table('queues', function (Blueprint $table) {
                $table->dropColumn('is_paused');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recréer les anciennes colonnes
        Schema::table('queues', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
            $table->boolean('is_paused')->default(false);
        });
        
        // Restaurer les valeurs depuis le statut
        \DB::table('queues')
            ->whereIn('status', ['open', 'paused', 'blocked'])
            ->update(['is_active' => true]);
            
        \DB::table('queues')
            ->where('status', 'paused')
            ->update(['is_paused' => true]);
            
        // Supprimer la colonne status
        Schema::table('queues', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
