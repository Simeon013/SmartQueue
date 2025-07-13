<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('queue_permissions', function (Blueprint $table) {
            // Supprimer la contrainte unique existante
            $table->dropUnique(['user_id', 'queue_id']);
            
            // Supprimer la contrainte foreign key existante
            $table->dropForeign(['user_id']);
            
            // Modifier la colonne pour permettre NULL
            $table->foreignId('user_id')->nullable()->change();
            
            // Recréer la contrainte foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Recréer la contrainte unique (maintenant user_id peut être NULL)
            $table->unique(['user_id', 'queue_id'], 'queue_permissions_user_queue_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queue_permissions', function (Blueprint $table) {
            // Supprimer la contrainte unique
            $table->dropUnique('queue_permissions_user_queue_unique');
            
            // Supprimer la contrainte foreign key
            $table->dropForeign(['user_id']);
            
            // Modifier la colonne pour ne plus permettre NULL
            $table->foreignId('user_id')->nullable(false)->change();
            
            // Recréer la contrainte foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Recréer la contrainte unique originale
            $table->unique(['user_id', 'queue_id']);
        });
    }
};
