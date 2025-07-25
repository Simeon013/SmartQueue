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
        Schema::create('ticket_cycles', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('current_cycle')->default(1);
            $table->timestamp('last_reset_at')->nullable();
            $table->timestamps();
        });

        // Insérer l'enregistrement initial
        DB::table('ticket_cycles')->insert([
            'current_cycle' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_cycles');
    }
};
