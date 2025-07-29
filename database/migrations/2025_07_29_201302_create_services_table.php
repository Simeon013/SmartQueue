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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->default('fa-solid fa-gear');
            $table->string('color')->default('#3b82f6'); // Couleur par défaut bleue
            $table->boolean('is_active')->default(true);
            $table->integer('position')->default(0); // Pour ordonner l'affichage des services
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
