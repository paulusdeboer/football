<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('rating_requests')
            ->whereIn('status', ['pending', 'expired', 'send_failed'])
            ->whereExists(function ($query): void {
                $query->select(DB::raw(1))
                    ->from('ratings')
                    ->whereColumn('ratings.game_id', 'rating_requests.game_id')
                    ->whereColumn('ratings.rating_player_id', 'rating_requests.player_id');
            })
            ->update([
                'status' => 'completed',
                'completed_at' => null,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // The original status cannot be restored reliably after this data fix.
    }
};
