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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('rating'); // Note de 1 à 5 étoiles
            $table->text('comment')->nullable(); // Commentaire optionnel
            $table->string('token')->unique(); // Token unique pour l'URL de l'avis
            $table->timestamp('submitted_at')->nullable(); // Date de soumission
            $table->timestamps();
            
            // Index pour les performances
            $table->index('ticket_id');
            $table->index('token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
