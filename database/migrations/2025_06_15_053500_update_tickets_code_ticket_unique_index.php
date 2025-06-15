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
            // Drop the existing unique index on 'code_ticket'
            $table->dropUnique(['code_ticket']);

            // Add a new unique composite index on 'queue_id' and 'code_ticket'
            $table->unique(['queue_id', 'code_ticket']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Reverse the changes by dropping the composite index
            $table->dropUnique(['queue_id', 'code_ticket']);

            // Re-add the unique index on 'code_ticket'
            $table->unique('code_ticket');
        });
    }
};
