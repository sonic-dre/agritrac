<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Expense;
use App\Models\ProduceType;
use App\Models\Transaction;
use App\Models\Trip;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class AgentTripSeeder extends Seeder
{
    // Exchange rate KES → UGX (derived from KDV trip: 135×3300 KSH = 7,099,600 UGX)
    private const RATE = 15.93;

    // Standard bag weight in kg
    private const BAG_KG = 90.0;
    private const BAGS   = 135;

    public function run(): void
    {
        $agent = Agent::where('email', 'sonicdrake7777@gmail.com')->first();
        if (! $agent) {
            $this->command->error('Agent sonicdrake7777@gmail.com not found. Create the agent first.');
            return;
        }

        $produce = ProduceType::where('name', 'like', '%aize%')->first()
            ?? ProduceType::where('name', 'like', '%rain%')->first()
            ?? ProduceType::first();

        $bagUnit = Unit::where('name', 'like', '%ag%')->first()
            ?? Unit::where('symbol', 'bag')->first()
            ?? Unit::first();

        $totalKg = self::BAGS * self::BAG_KG;

        $trips = [
            // Image 1 — KDM (note: written as 2024 in notebook but treated as 2026 collection)
            [
                'region'   => 'KDM',
                'date'     => '2026-03-16',
                'buy_kes'  => 3500,
                'tr_kes'   => 100000,
                'gate_kes' => 5000,
                'busia'    => 400000,
                'revenue'  => 19405000,
                'extras'   => [],
                'sells'    => [],  // single aggregate sell
            ],
            // Images 2 & 4 — KDV, with detailed sell breakdown from image 4
            [
                'region'   => 'KDV',
                'date'     => '2026-05-26',
                'buy_kes'  => 3300,
                'tr_kes'   => 100000,
                'gate_kes' => 5000,
                'busia'    => 400000,
                'revenue'  => 13475000,
                'extras'   => [
                    ['label' => 'ABU',            'category' => 'other', 'amount' => 400000],
                    ['label' => 'Works & Driver', 'category' => 'other', 'amount' => 410000],
                ],
                // 27+4+66+17+2+6+3+1+9 = 135 bags, total = 13,475,000 UGX
                'sells' => [
                    ['bags' => 27, 'price' => 120000],
                    ['bags' =>  4, 'price' => 115000],
                    ['bags' => 66, 'price' => 110000],
                    ['bags' => 17, 'price' => 100000],
                    ['bags' =>  2, 'price' =>  70000],
                    ['bags' =>  6, 'price' =>  50000],
                    ['bags' =>  3, 'price' =>  40000],
                    ['bags' =>  1, 'price' =>  30000],
                    ['bags' =>  9, 'price' =>  25000], // remainder (225,000 UGX)
                ],
            ],
            // Image 3 — KC4
            [
                'region'   => 'KC4',
                'date'     => '2026-05-24',
                'buy_kes'  => 3000,
                'tr_kes'   => 100000,
                'gate_kes' => 5000,
                'busia'    => 400000,
                'revenue'  => 14260000,
                'extras'   => [],
                'sells'    => [],
            ],
        ];

        foreach ($trips as $td) {
            // Skip if already seeded
            if (Trip::where('agent_id', $agent->id)->where('region', $td['region'])->where('start_date', $td['date'])->exists()) {
                $this->command->warn("Skipping {$td['region']} {$td['date']} — already exists.");
                continue;
            }

            $buyUgx       = (int)(self::BAGS * $td['buy_kes'] * self::RATE);
            $transportUgx = (int)($td['tr_kes'] * self::RATE);
            $gateUgx      = (int)($td['gate_kes'] * self::RATE);
            $extraTotal   = (int) array_sum(array_column($td['extras'], 'amount'));
            $amountSpent  = $buyUgx + $transportUgx + $gateUgx + $td['busia'] + $extraTotal;

            $trip = Trip::create([
                'agent_id'      => $agent->id,
                'region'        => $td['region'],
                'produce_list'  => [$produce?->name ?? 'Maize'],
                'start_date'    => $td['date'],
                'total_days'    => 5,
                'current_day'   => 5,
                'status'        => 'completed',
                'sync_status'   => 'synced',
                'tonnage_kg'    => $totalKg,
                'revenue'       => $td['revenue'],
                'amount_spent'  => $amountSpent,
                'currency'      => 'UGX',
                'exchange_rate' => self::RATE,
            ]);

            // ── Buy transaction (price in KES, total converted to UGX) ────────
            Transaction::create([
                'trip_id'          => $trip->id,
                'agent_id'         => $agent->id,
                'produce_type_id'  => $produce?->id,
                'type'             => 'buy',
                'quantity_kg'      => $totalKg,
                'unit_id'          => $bagUnit?->id,
                'unit_price'       => $td['buy_kes'],
                'total_amount'     => $buyUgx,
                'currency'         => 'KES',
                'location'         => $td['region'],
                'transaction_date' => $td['date'],
                'sync_status'      => 'synced',
            ]);

            // ── Sell transactions ─────────────────────────────────────────────
            if (! empty($td['sells'])) {
                foreach ($td['sells'] as $s) {
                    Transaction::create([
                        'trip_id'          => $trip->id,
                        'agent_id'         => $agent->id,
                        'produce_type_id'  => $produce?->id,
                        'type'             => 'sell',
                        'quantity_kg'      => $s['bags'] * self::BAG_KG,
                        'unit_id'          => $bagUnit?->id,
                        'unit_price'       => $s['price'],
                        'total_amount'     => $s['bags'] * $s['price'],
                        'currency'         => 'UGX',
                        'location'         => $td['region'],
                        'transaction_date' => $td['date'],
                        'sync_status'      => 'synced',
                    ]);
                }
            } else {
                Transaction::create([
                    'trip_id'          => $trip->id,
                    'agent_id'         => $agent->id,
                    'produce_type_id'  => $produce?->id,
                    'type'             => 'sell',
                    'quantity_kg'      => $totalKg,
                    'unit_id'          => $bagUnit?->id,
                    'unit_price'       => (int)($td['revenue'] / self::BAGS),
                    'total_amount'     => $td['revenue'],
                    'currency'         => 'UGX',
                    'location'         => $td['region'],
                    'transaction_date' => $td['date'],
                    'sync_status'      => 'synced',
                ]);
            }

            // ── Standard expenses ─────────────────────────────────────────────
            Expense::create([
                'trip_id'      => $trip->id,
                'category'     => 'transport',
                'label'        => 'Transport',
                'amount'       => $transportUgx,
                'currency'     => 'UGX',
                'expense_date' => $td['date'],
            ]);
            Expense::create([
                'trip_id'      => $trip->id,
                'category'     => 'transport',
                'label'        => 'Gate Fee',
                'amount'       => $gateUgx,
                'currency'     => 'UGX',
                'expense_date' => $td['date'],
            ]);
            Expense::create([
                'trip_id'      => $trip->id,
                'category'     => 'transport',
                'label'        => 'Busia Border',
                'amount'       => $td['busia'],
                'currency'     => 'UGX',
                'expense_date' => $td['date'],
            ]);

            foreach ($td['extras'] as $ex) {
                Expense::create([
                    'trip_id'      => $trip->id,
                    'category'     => $ex['category'],
                    'label'        => $ex['label'],
                    'amount'       => $ex['amount'],
                    'currency'     => 'UGX',
                    'expense_date' => $td['date'],
                ]);
            }

            $this->command->info("Created trip {$td['region']} {$td['date']} (id={$trip->id})");
        }
    }
}
