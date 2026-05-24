<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $agent = DB::table('agents')
            ->whereRaw('LOWER(name) LIKE ?', ['%drake%'])
            ->first();

        if (! $agent) {
            return; // Agent not found — skip silently
        }

        // Skip if Drake already has multiple active trips
        $existing = DB::table('trips')
            ->where('agent_id', $agent->id)
            ->whereIn('status', ['departing', 'in_progress', 'returning'])
            ->count();

        if ($existing >= 3) {
            return;
        }

        $today = Carbon::today();
        $now   = Carbon::now();

        $trips = [
            [
                'agent_id'         => $agent->id,
                'region'           => 'Mbale / Sironko',
                'produce_list'     => json_encode(['Irish Potatoes', 'Beans (K132)']),
                'start_date'       => $today->copy()->subDays(2)->format('Y-m-d'),
                'total_days'       => 5,
                'current_day'      => 3,
                'status'           => 'in_progress',
                'sync_status'      => 'synced',
                'offline_hours'    => 0,
                'unsynced_records' => 0,
                'tonnage_kg'       => 8400,
                'amount_spent'     => 11200000,
                'advance_amount'   => 18000000,
                'payment_type'     => 'advance',
                'currency'         => 'UGX',
                'revenue'          => 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'agent_id'         => $agent->id,
                'region'           => 'Gulu / Lira',
                'produce_list'     => json_encode(['Simsim', 'Groundnuts']),
                'start_date'       => $today->copy()->format('Y-m-d'),
                'total_days'       => 4,
                'current_day'      => 1,
                'status'           => 'departing',
                'sync_status'      => 'synced',
                'offline_hours'    => 0,
                'unsynced_records' => 0,
                'tonnage_kg'       => 0,
                'amount_spent'     => 0,
                'advance_amount'   => 12000000,
                'payment_type'     => 'advance',
                'currency'         => 'UGX',
                'revenue'          => 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'agent_id'         => $agent->id,
                'region'           => 'Kasese / Fort Portal',
                'produce_list'     => json_encode(['Maize (Dry)', 'Sweet Potatoes']),
                'start_date'       => $today->copy()->subDays(4)->format('Y-m-d'),
                'total_days'       => 5,
                'current_day'      => 4,
                'status'           => 'returning',
                'sync_status'      => 'pending',
                'offline_hours'    => 3,
                'unsynced_records' => 4,
                'tonnage_kg'       => 14200,
                'amount_spent'     => 19800000,
                'advance_amount'   => 22000000,
                'payment_type'     => 'advance',
                'currency'         => 'UGX',
                'revenue'          => 0,
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ];

        foreach ($trips as $trip) {
            DB::table('trips')->insert($trip);
        }
    }

    public function down(): void
    {
        $agent = DB::table('agents')
            ->whereRaw('LOWER(name) LIKE ?', ['%drake%'])
            ->first();

        if ($agent) {
            DB::table('trips')
                ->where('agent_id', $agent->id)
                ->whereIn('region', ['Mbale / Sironko', 'Gulu / Lira', 'Kasese / Fort Portal'])
                ->delete();
        }
    }
};
