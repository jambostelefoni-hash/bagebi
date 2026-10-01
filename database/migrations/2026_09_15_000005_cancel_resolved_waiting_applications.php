<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('kindergarteners')
            ->where('application_status', 'waiting')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('waiting_list_entries')
                    ->whereColumn('waiting_list_entries.kindergartener_id', 'kindergarteners.id')
                    ->whereIn('waiting_list_entries.state', ['declined', 'expired']);
            })
            ->update([
                'application_status' => 'cancelled',
                'active_status_id' => 4,
                'status_changed_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Historical queue resolution must not be reversed automatically.
    }
};
