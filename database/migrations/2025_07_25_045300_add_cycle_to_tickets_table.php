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
        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedInteger('cycle')->default(1)->after('status');
        });

        // Créer un index composite pour optimiser les recherches
        Schema::table('tickets', function (Blueprint $table) {
            $table->index(['queue_id', 'cycle']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['queue_id', 'cycle']);
            $table->dropColumn('cycle');
        });
    }
};
