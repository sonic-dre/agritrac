<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if geo data already exists
        if (DB::table('transactions')->whereNotNull('latitude')->exists()) {
            return;
        }

        $agents   = DB::table('agents')->orderBy('id')->limit(6)->get();
        $produces = DB::table('produce_types')->orderBy('id')->get()->keyBy('id');
        $trips    = DB::table('trips')->orderBy('id')->limit(6)->get()->keyBy('agent_id');

        if ($agents->isEmpty() || $produces->isEmpty()) {
            return;
        }

        $agentList = $agents->values();
        $now       = Carbon::now();
        $today     = Carbon::today();

        // [label, lat, lng, country, produce_slug_hints, agent_idx]
        $points = [
            // ─── UGANDA ──────────────────────────────────────────────────────────
            // Eastern Uganda — Robert Wafula (agent 0)
            ['Mbale Market',    1.0808,  34.1751,  'UGX', 1,     0, 2800, 1450],
            ['Sironko',         1.2319,  34.2468,  'UGX', 1,     0, 2400, 850],
            ['Kapchorwa',       1.3988,  34.4528,  'UGX', 2,     0, 380,  4200],
            ['Manafwa',         0.9754,  34.2905,  'UGX', 3,     0, 1100, 3100],
            ['Bududa',          1.0002,  34.3290,  'UGX', 1,     0, 1800, 860],

            // Northern Uganda — Sarah Akello (agent 1)
            ['Gulu Market',     2.7747,  32.3023,  'UGX', 3,     1, 1200, 3100],
            ['Lira Town',       2.2499,  32.9000,  'UGX', 5,     1, 920,  8500],
            ['Kitgum',          3.2779,  32.8885,  'UGX', 5,     1, 650,  8200],
            ['Pader',           2.7713,  33.1863,  'UGX', 3,     1, 780,  3000],

            // Western Uganda — John Tumusiime (agent 2)
            ['Kasese',          0.1836,  30.0827,  'UGX', 4,     2, 3200, 1450],
            ['Fort Portal',     0.6674,  30.2749,  'UGX', 4,     2, 2100, 1400],
            ['Kyenjojo',        0.6118,  30.6277,  'UGX', 1,     2, 1400, 840],
            ['Bundibugyo',      0.7178,  30.0613,  'UGX', 4,     2, 1800, 1420],

            // Eastern — Moses Ochieng (agent 3)
            ['Soroti',          1.7143,  33.6112,  'UGX', 3,     3, 900,  2800],
            ['Kumi',            1.4617,  33.9372,  'UGX', 3,     3, 1100, 3050],
            ['Serere',          1.5021,  33.5419,  'UGX', 3,     3, 750,  2900],

            // Central Uganda — Grace Nakato (agent 4)
            ['Masaka',         -0.3133,  31.7359,  'UGX', 1,     4, 4820, 850],
            ['Mbarara',        -0.6068,  30.6557,  'UGX', 6,     4, 2300, 1100],
            ['Lyantonde',      -0.4043,  31.1555,  'UGX', 1,     4, 1600, 860],
            ['Rakai',          -0.6967,  31.4037,  'UGX', 6,     4, 1200, 1080],

            // West Nile — Peter Opolot (agent 5)
            ['Arua',            3.0227,  30.9100,  'UGX', 5,     5, 1800, 8500],
            ['Koboko',          3.4150,  30.9642,  'UGX', 5,     5, 1100, 8300],
            ['Nebbi',           2.4797,  31.0850,  'UGX', 3,     5, 900,  3100],
            ['Yumbe',           3.4603,  31.2452,  'UGX', 5,     5, 750,  8100],

            // Cross-border / Central
            ['Jinja',           0.4366,  33.2031,  'UGX', 4,     0, 2200, 1430],
            ['Kampala (HQ)',    0.3476,  32.5825,  'UGX', 3,     1, 0,    0],   // advance

            // ─── KENYA ───────────────────────────────────────────────────────────
            ['Kisumu Port',    -0.1022,  34.7617,  'KES', 4,     2, 4100, 62],
            ['Eldoret',         0.5143,  35.2698,  'KES', 4,     0, 3800, 58],
            ['Kitale',          1.0197,  35.0062,  'KES', 4,     1, 2900, 61],
            ['Kakamega',        0.2827,  34.7519,  'KES', 4,     3, 2100, 60],
            ['Nakuru',         -0.3031,  36.0800,  'KES', 1,     4, 3200, 35],
            ['Kericho',        -0.3670,  35.2828,  'KES', 4,     5, 1800, 59],
            ['Busia Border',    0.4617,  34.0900,  'KES', 3,     2, 1400, 135],
            ['Nairobi Wakulima',-1.2921, 36.8219,  'KES', 3,     3, 5200, 140],
            ['Meru',           -0.0500,  37.6490,  'KES', 1,     1, 2600, 38],
            ['Thika',          -1.0332,  37.0694,  'KES', 3,     0, 1900, 138],

            // ─── TANZANIA ────────────────────────────────────────────────────────
            ['Arusha Market',  -3.3869,  36.6830,  'TZS', 4,     0, 5400, 850],
            ['Moshi',          -3.3349,  37.3410,  'TZS', 1,     1, 3800, 780],
            ['Mwanza',         -2.5164,  32.9175,  'TZS', 4,     2, 6200, 820],
            ['Dar es Salaam',  -6.7924,  39.2083,  'TZS', 3,     3, 4100, 3200],
            ['Dodoma',         -6.1722,  35.7395,  'TZS', 4,     4, 2800, 800],
            ['Iringa',         -7.7675,  35.6934,  'TZS', 4,     5, 3100, 815],
            ['Morogoro',       -6.8241,  37.6606,  'TZS', 3,     0, 2200, 3100],
            ['Tanga',          -5.0780,  39.1026,  'TZS', 3,     1, 1900, 3150],
            ['Kilimanjaro',    -3.0674,  37.3556,  'TZS', 1,     2, 4400, 790],
            ['Bukoba',         -1.3320,  31.8131,  'TZS', 4,     4, 2700, 810],
        ];

        $rows = [];
        foreach ($points as $i => [$location, $lat, $lng, $currency, $produceIdx, $agentIdx, $qty, $price]) {
            if ($agentIdx >= $agentList->count()) continue;

            $agent      = $agentList[$agentIdx];
            $produceType = $produces->get($produceIdx);
            $trip        = $trips->get($agent->id);
            $tripId      = $trip?->id ?? null;

            // Kampala HQ row = advance
            $isAdvance = ($qty === 0);
            $type      = $isAdvance ? 'advance' : 'purchase';
            $qtyKg     = $isAdvance ? null : (float) $qty;
            $unitPrice = $isAdvance ? null : (int) $price;
            $total     = $isAdvance ? 20000000 : -(int)($qty * $price);

            $moisture = null;
            if (!$isAdvance && in_array($currency, ['UGX', 'KES'])) {
                $moisture = round(11.5 + ($i % 7) * 0.8, 1); // 11.5 – 16.3 %
            }

            $rows[] = [
                'trip_id'          => $tripId,
                'agent_id'         => $agent->id,
                'produce_type_id'  => $isAdvance ? null : $produceType?->id,
                'type'             => $type,
                'quantity_kg'      => $qtyKg,
                'unit_price'       => $unitPrice,
                'total_amount'     => $total,
                'currency'         => $currency,
                'location'         => $location,
                'category'         => $isAdvance ? 'advance' : 'purchase',
                'transaction_date' => $today->copy()->subDays($i % 12)->format('Y-m-d'),
                'sync_status'      => ['synced', 'synced', 'synced', 'pending'][$i % 4],
                'notes'            => null,
                'latitude'         => $lat,
                'longitude'        => $lng,
                'moisture_content' => $moisture,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        foreach (array_chunk($rows, 20) as $chunk) {
            DB::table('transactions')->insert($chunk);
        }
    }

    public function down(): void
    {
        DB::table('transactions')->whereNotNull('latitude')->delete();
    }
};
