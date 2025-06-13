<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_id')->constrained()->onDelete('cascade');
            $table->string('code_ticket')->unique();
            $table->integer('number')->nullable();
            $table->enum('status', ['waiting', 'called', 'served', 'skipped'])->default('waiting');
            $table->boolean('wants_notifications')->default(false);
            $table->string('notification_channel')->nullable(); // 'email' ou 'sms'
            $table->timestamp('called_at')->nullable();
            $table->timestamp('served_at')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamps();

            // Index pour améliorer les performances
            $table->index(['queue_id', 'status']);
            $table->index(['queue_id', 'created_at']);
            $table->index('session_id');
            $table->index('number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
}