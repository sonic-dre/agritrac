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
            return;
        }

        // Idempotent — skip if Drake already has geotagged transactions
        if (DB::table('transactions')->where('agent_id', $agent->id)->whereNotNull('latitude')->exists()) {
            return;
        }

        $produces = DB::table('produce_types')->get()->keyBy('slug');
        $trips    = DB::table('trips')->where('agent_id', $agent->id)->get()->keyBy('region');

        $mbale   = $trips->get('Mbale / Sironko');
        $gulu    = $trips->get('Gulu / Lira');
        $kasese  = $trips->get('Kasese / Fort Portal');

        $potatoes    = $produces->get('potatoes');
        $beans       = $produces->get('beans');
        $simsim      = $produces->get('simsim');
        $groundnuts  = $produces->get('groundnuts');
        $maize       = $produces->get('maize');
        $sweetPot    = $produces->get('sweet-potatoes');

        $now   = Carbon::now();
        $today = Carbon::today();

        $rows = [];

        // ─── Mbale / Sironko — in_progress, day 3/5 ─────────────────────────────
        if ($mbale) {
            $rows[] = $this->purchase($agent->id, $mbale->id, $potatoes, 1.0808, 34.1751, 'Mbale Market',   2800, 850,  'UGX', $today->copy()->subDays(2), $now, 12.4);
            $rows[] = $this->purchase($agent->id, $mbale->id, $beans,    1.2319, 34.2468, 'Sironko',        2400, 860,  'UGX', $today->copy()->subDays(2), $now, 13.1);
            $rows[] = $this->purchase($agent->id, $mbale->id, $potatoes, 1.3988, 34.4528, 'Kapchorwa',      1800, 840,  'UGX', $today->copy()->subDays(1), $now, 12.8);
            $rows[] = $this->purchase($agent->id, $mbale->id, $beans,    0.9754, 34.2905, 'Manafwa',        1400, 850,  'UGX', $today->copy()->subDays(1), $now, 13.5);
        }

        // ─── Gulu / Lira — departing, day 1/4 ───────────────────────────────────
        if ($gulu) {
            // Just arrived — single purchase to mark location; mostly in transit
            $rows[] = $this->purchase($agent->id, $gulu->id, $simsim,    2.7747, 32.3023, 'Gulu Market',    320,  8400, 'UGX', $today->copy(),            $now, null);
            $rows[] = $this->purchase($agent->id, $gulu->id, $groundnuts, 2.2499, 32.9000, 'Lira Town',     280,  4200, 'UGX', $today->copy(),            $now, null);
        }

        // ─── Kasese / Fort Portal — returning, day 4/5 ──────────────────────────
        if ($kasese) {
            $rows[] = $this->purchase($agent->id, $kasese->id, $maize,    0.1836, 30.0827, 'Kasese',         3200, 1450, 'UGX', $today->copy()->subDays(4), $now, 14.2);
            $rows[] = $this->purchase($agent->id, $kasese->id, $sweetPot, 0.6674, 30.2749, 'Fort Portal',    2100, 1100, 'UGX', $today->copy()->subDays(3), $now, 11.8);
            $rows[] = $this->purchase($agent->id, $kasese->id, $maize,    0.6118, 30.6277, 'Kyenjojo',       2800, 1420, 'UGX', $today->copy()->subDays(3), $now, 13.6);
            $rows[] = $this->purchase($agent->id, $kasese->id, $sweetPot, 0.7178, 30.0613, 'Bundibugyo',     2400, 1080, 'UGX', $today->copy()->subDays(2), $now, 12.1);
            $rows[] = $this->purchase($agent->id, $kasese->id, $maize,    0.2690, 30.1420, 'Kasese (South)', 3700, 1410, 'UGX', $today->copy()->subDays(1), $now, 15.0);
        }

        foreach (array_chunk($rows, 20) as $chunk) {
            DB::table('transactions')->insert($chunk);
        }
    }

    private function purchase(
        int $agentId, int $tripId, ?object $produce,
        float $lat, float $lng, string $location,
        float $qty, int $price, string $currency,
        Carbon $date, Carbon $now, ?float $moisture
    ): array {
        return [
            'trip_id'          => $tripId,
            'agent_id'         => $agentId,
            'produce_type_id'  => $produce?->id,
            'type'             => 'purchase',
            'quantity_kg'      => $qty,
            'unit_price'       => $price,
            'total_amount'     => -(int)($qty * $price),
            'currency'         => $currency,
            'location'         => $location,
            'category'         => 'purchase',
            'transaction_date' => $date->format('Y-m-d'),
            'sync_status'      => 'synced',
            'notes'            => null,
            'latitude'         => $lat,
            'longitude'        => $lng,
            'moisture_content' => $moisture,
            'created_at'       => $now,
            'updated_at'       => $now,
        ];
    }

    public function down(): void
    {
        $agent = DB::table('agents')
            ->whereRaw('LOWER(name) LIKE ?', ['%drake%'])
            ->first();

        if ($agent) {
            DB::table('transactions')
                ->where('agent_id', $agent->id)
                ->whereNotNull('latitude')
                ->delete();
        }
    }
};
