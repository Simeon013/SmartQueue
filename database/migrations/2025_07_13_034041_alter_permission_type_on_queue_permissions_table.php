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
            $table->string('permission_type', 32)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queue_permissions', function (Blueprint $table) {
            $table->string('permission_type', 8)->change(); // Remettre à la taille d'origine si besoin
        });
    }
};
