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
        Schema::create('queue_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('queue_id')->constrained()->onDelete('cascade');
            $table->enum('permission_type', ['owner', 'manager', 'operator']);
            $table->foreignId('granted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Un utilisateur ne peut avoir qu'un seul type de permission par file
            $table->unique(['user_id', 'queue_id']);

            // Index pour améliorer les performances
            $table->index(['user_id', 'permission_type']);
            $table->index(['queue_id', 'permission_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_permissions');
    }
};
