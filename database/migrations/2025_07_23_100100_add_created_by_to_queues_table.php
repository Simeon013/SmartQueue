<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Queue;
use App\Models\QueuePermission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ajouter la colonne created_by
        Schema::table('queues', function (Blueprint $table) {
            $table->foreignId('created_by')
                  ->nullable()
                  ->after('is_active')
                  ->constrained('users')
                  ->onDelete('set null');
        });

        // Mettre à jour les files existantes avec le premier propriétaire trouvé
        $queues = Queue::all();
        foreach ($queues as $queue) {
            $owner = QueuePermission::where('queue_id', $queue->id)
                ->where('permission_type', 'owner')
                ->orderBy('created_at')
                ->first();

            if ($owner) {
                $queue->created_by = $owner->user_id;
                $queue->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};
