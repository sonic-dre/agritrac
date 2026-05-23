<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Expense;
use App\Models\PriceRecord;
use App\Models\ProduceType;
use App\Models\SyncRecord;
use App\Models\Transaction;
use App\Models\Trip;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $this->seedUnits();
        $this->seedProduceTypes();
        $this->seedAgents();
        $this->seedTrips();
        $this->seedTransactions();
        $this->seedExpenses();
        $this->seedPriceRecords();
        $this->seedSyncRecords();
    }

    private function seedUsers(): void
    {
        User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@agritrack.ug',
            'password' => 'password',
            'role'     => 'admin',
        ]);
        User::create([
            'name'     => 'Operations Manager',
            'email'    => 'manager@agritrack.ug',
            'password' => 'password',
            'role'     => 'manager',
        ]);
        User::create([
            'name'     => 'Field Viewer',
            'email'    => 'viewer@agritrack.ug',
            'password' => 'password',
            'role'     => 'viewer',
        ]);
    }

    private function seedUnits(): void
    {
        $units = [
            ['name' => 'Kilogram',       'symbol' => 'kg',     'base_kg' => 1.0],
            ['name' => 'Metric Tonne',   'symbol' => 'MT',     'base_kg' => 1000.0],
            ['name' => '90kg Sack',      'symbol' => '90kg sack', 'base_kg' => 90.0],
            ['name' => '50kg Bag',       'symbol' => '50kg bag',  'base_kg' => 50.0],
            ['name' => 'Crate',          'symbol' => 'crate',  'base_kg' => null],
            ['name' => 'Litre',          'symbol' => 'L',      'base_kg' => null],
        ];
        foreach ($units as $u) {
            Unit::create($u);
        }
    }

    private function seedProduceTypes(): void
    {
        $types = [
            ['name' => 'Irish Potatoes', 'emoji' => '🥔', 'slug' => 'potatoes',      'current_price' => 850,   'change_percent' => 5.2,  'signal' => 'buy',  'primary_location' => 'Sironko',    'accent_color' => '#d29922'],
            ['name' => 'Groundnuts',     'emoji' => '🥜', 'slug' => 'groundnuts',    'current_price' => 4200,  'change_percent' => -1.8, 'signal' => 'hold', 'primary_location' => 'Kapchorwa',  'accent_color' => '#3fb950'],
            ['name' => 'Beans (K132)',   'emoji' => '🫘', 'slug' => 'beans',         'current_price' => 3100,  'change_percent' => 2.1,  'signal' => 'buy',  'primary_location' => 'Kumi',       'accent_color' => '#58a6ff'],
            ['name' => 'Maize (Dry)',    'emoji' => '🌽', 'slug' => 'maize',         'current_price' => 1450,  'change_percent' => 0.8,  'signal' => 'hold', 'primary_location' => 'Kasese',     'accent_color' => '#f0883e'],
            ['name' => 'Simsim',         'emoji' => '🌿', 'slug' => 'simsim',        'current_price' => 8500,  'change_percent' => 3.4,  'signal' => 'buy',  'primary_location' => 'Lira',       'accent_color' => '#bc8cff'],
            ['name' => 'Sweet Potatoes', 'emoji' => '🍠', 'slug' => 'sweet-potatoes','current_price' => 1100,  'change_percent' => -4.2, 'signal' => 'sell', 'primary_location' => 'Masaka',     'accent_color' => '#f85149'],
        ];
        foreach ($types as $t) {
            ProduceType::create($t);
        }
    }

    private function seedAgents(): void
    {
        $agents = [
            ['name' => 'Robert Wafula',  'initials' => 'RW', 'region' => 'Eastern Uganda',  'base_location' => 'Mbale',    'avatar_color' => '#3fb950'],
            ['name' => 'Sarah Akello',   'initials' => 'SA', 'region' => 'Northern Uganda', 'base_location' => 'Gulu',     'avatar_color' => '#bc8cff'],
            ['name' => 'John Tumusiime', 'initials' => 'JT', 'region' => 'Western Uganda',  'base_location' => 'Kasese',   'avatar_color' => '#f0883e'],
            ['name' => 'Moses Ochieng',  'initials' => 'MO', 'region' => 'Eastern Uganda',  'base_location' => 'Soroti',   'avatar_color' => '#58a6ff'],
            ['name' => 'Grace Nakato',   'initials' => 'GN', 'region' => 'Central Uganda',  'base_location' => 'Masaka',   'avatar_color' => '#d29922'],
            ['name' => 'Peter Opolot',   'initials' => 'PO', 'region' => 'West Nile',       'base_location' => 'Arua',     'avatar_color' => '#f85149'],
        ];
        foreach ($agents as $a) {
            Agent::create($a);
        }
    }

    private function seedTrips(): void
    {
        $today = Carbon::today();

        $trips = [
            // Robert Wafula - Day 3/5 - offline 2h - 12.4T - 16.7M spent
            [
                'agent_id'        => 1,
                'region'          => 'Mbale / Sironko',
                'produce_list'    => ['Potatoes', 'Gnuts', 'Beans'],
                'start_date'      => $today->copy()->subDays(2),
                'total_days'      => 5,
                'current_day'     => 3,
                'status'          => 'in_progress',
                'sync_status'     => 'offline',
                'offline_hours'   => 2,
                'unsynced_records'=> 0,
                'tonnage_kg'      => 12400,
                'amount_spent'    => 16700000,
                'advance_amount'  => 20000000,
                'revenue'         => 0,
            ],
            // Sarah Akello - Day 1/4 - pending 1h - 6.8T - 7.2M
            [
                'agent_id'        => 2,
                'region'          => 'Gulu / Lira',
                'produce_list'    => ['Beans', 'Simsim'],
                'start_date'      => $today->copy(),
                'total_days'      => 4,
                'current_day'     => 1,
                'status'          => 'in_progress',
                'sync_status'     => 'pending',
                'offline_hours'   => 1,
                'unsynced_records'=> 2,
                'tonnage_kg'      => 6800,
                'amount_spent'    => 7200000,
                'advance_amount'  => 15000000,
                'revenue'         => 0,
            ],
            // John Tumusiime - Day 2/5 - offline 6h - 3 unsynced - 9.2T - 11.4M
            [
                'agent_id'        => 3,
                'region'          => 'Kasese / Fort Portal',
                'produce_list'    => ['Maize', 'Potatoes'],
                'start_date'      => $today->copy()->subDays(1),
                'total_days'      => 5,
                'current_day'     => 2,
                'status'          => 'in_progress',
                'sync_status'     => 'offline',
                'offline_hours'   => 6,
                'unsynced_records'=> 3,
                'tonnage_kg'      => 9200,
                'amount_spent'    => 11400000,
                'advance_amount'  => 18000000,
                'revenue'         => 0,
            ],
            // Moses Ochieng - Day 0/4 - synced - departing
            [
                'agent_id'        => 4,
                'region'          => 'Soroti / Kumi',
                'produce_list'    => ['Potatoes', 'Beans'],
                'start_date'      => $today->copy(),
                'total_days'      => 4,
                'current_day'     => 0,
                'status'          => 'departing',
                'sync_status'     => 'synced',
                'offline_hours'   => 0,
                'unsynced_records'=> 0,
                'tonnage_kg'      => 0,
                'amount_spent'    => 0,
                'advance_amount'  => 12000000,
                'revenue'         => 0,
            ],
            // Grace Nakato - Day 4/5 - synced - returning - 7.1T - 6.8M
            [
                'agent_id'        => 5,
                'region'          => 'Masaka / Mbarara',
                'produce_list'    => ['Potatoes', 'Sweet Potatoes'],
                'start_date'      => $today->copy()->subDays(3),
                'total_days'      => 5,
                'current_day'     => 4,
                'status'          => 'returning',
                'sync_status'     => 'synced',
                'offline_hours'   => 0,
                'unsynced_records'=> 0,
                'tonnage_kg'      => 7100,
                'amount_spent'    => 6800000,
                'advance_amount'  => 10000000,
                'revenue'         => 0,
            ],
            // Peter Opolot - Day 2/6 - pending - 8.9T - 19.2M
            [
                'agent_id'        => 6,
                'region'          => 'Arua / West Nile',
                'produce_list'    => ['Simsim', 'Beans', 'Gnuts'],
                'start_date'      => $today->copy()->subDays(1),
                'total_days'      => 6,
                'current_day'     => 2,
                'status'          => 'in_progress',
                'sync_status'     => 'pending',
                'offline_hours'   => 0,
                'unsynced_records'=> 2,
                'tonnage_kg'      => 8900,
                'amount_spent'    => 19200000,
                'advance_amount'  => 25000000,
                'revenue'         => 0,
            ],
        ];

        // Seed completed trips from May 2026 (for accounting charts)
        foreach ($trips as $t) {
            Trip::create($t);
        }

        // 16 historical completed trips in May 2026
        $agents = [1, 2, 3, 4, 5, 6];
        $revenues = [38200000, 42100000, 35800000, 48500000, 44200000, 52100000, 46800000, 54300000, 50100000, 57800000, 39000000, 43000000, 36500000, 49000000, 45000000, 53000000];
        $costs    = [28100000, 30500000, 26200000, 35100000, 31800000, 37400000, 33500000, 38900000, 36200000, 41400000, 29000000, 31000000, 27000000, 36000000, 32000000, 38000000];

        for ($i = 0; $i < 16; $i++) {
            Trip::create([
                'agent_id'      => $agents[$i % 6],
                'region'        => 'Various',
                'produce_list'  => ['Potatoes', 'Beans'],
                'start_date'    => $today->copy()->subDays(rand(5, 25)),
                'total_days'    => 4,
                'current_day'   => 4,
                'status'        => 'completed',
                'sync_status'   => 'synced',
                'tonnage_kg'    => rand(5000, 15000),
                'amount_spent'  => $costs[$i],
                'revenue'       => $revenues[$i],
            ]);
        }
    }

    private function seedTransactions(): void
    {
        $today = Carbon::today();

        $rows = [
            ['trip_id' => 1, 'agent_id' => 1, 'produce_type_id' => 1, 'type' => 'purchase', 'quantity_kg' => 2400, 'unit_price' => 850,  'total_amount' => -2040000,   'location' => 'Sironko',        'sync_status' => 'offline', 'notes' => null, 'transaction_date' => $today->copy()],
            ['trip_id' => 1, 'agent_id' => 1, 'produce_type_id' => 2, 'type' => 'purchase', 'quantity_kg' => 380,  'unit_price' => 4200, 'total_amount' => -1596000,   'location' => 'Kapchorwa',      'sync_status' => 'offline', 'notes' => null, 'transaction_date' => $today->copy()],
            ['trip_id' => 1, 'agent_id' => 1, 'produce_type_id' => null,'type'=> 'expense',  'quantity_kg' => null, 'unit_price' => null, 'total_amount' => -320000,    'location' => 'Total Mbale',    'sync_status' => 'pending', 'category' => 'fuel', 'notes' => null, 'transaction_date' => $today->copy()],
            ['trip_id' => 2, 'agent_id' => 2, 'produce_type_id' => 3, 'type' => 'purchase', 'quantity_kg' => 1200, 'unit_price' => 3100, 'total_amount' => -3720000,   'location' => 'Gulu Market',    'sync_status' => 'synced',  'notes' => null, 'transaction_date' => $today->copy()->subDays(1)],
            ['trip_id' => 3, 'agent_id' => 3, 'produce_type_id' => 4, 'type' => 'purchase', 'quantity_kg' => 3200, 'unit_price' => 1450, 'total_amount' => -4640000,   'location' => 'Kasese',         'sync_status' => 'offline', 'notes' => '6h', 'transaction_date' => $today->copy()->subDays(1)],
            ['trip_id' => null,'agent_id'=> null,'produce_type_id'=> null,'type'=> 'advance','quantity_kg'=> null,'unit_price'=> null,'total_amount'=> 25000000,  'location' => 'Kampala',       'sync_status' => 'synced',  'notes' => null, 'transaction_date' => $today->copy()->subDays(1)],
            ['trip_id' => 5, 'agent_id' => 5, 'produce_type_id' => 1, 'type' => 'purchase', 'quantity_kg' => 4820, 'unit_price' => 850,  'total_amount' => -4097000,   'location' => 'Masaka',         'sync_status' => 'synced',  'notes' => null, 'transaction_date' => $today->copy()->subDays(2)],
            ['trip_id' => 6, 'agent_id' => 6, 'produce_type_id' => 5, 'type' => 'purchase', 'quantity_kg' => 1800, 'unit_price' => 8100, 'total_amount' => -14580000,  'location' => 'Arua',           'sync_status' => 'pending', 'notes' => null, 'transaction_date' => $today->copy()->subDays(2)],
            ['trip_id' => 4, 'agent_id' => 4, 'produce_type_id' => 3, 'type' => 'purchase', 'quantity_kg' => 900,  'unit_price' => 2800, 'total_amount' => -2520000,   'location' => 'Kumi',           'sync_status' => 'synced',  'notes' => null, 'transaction_date' => $today->copy()->subDays(3)],
            ['trip_id' => 1, 'agent_id' => 1, 'produce_type_id' => null,'type'=> 'expense',  'quantity_kg' => null, 'unit_price' => null, 'total_amount' => -450000,    'location' => 'Sironko',        'sync_status' => 'synced',  'category' => 'labour', 'notes' => null, 'transaction_date' => $today->copy()->subDays(3)],
        ];

        foreach ($rows as $r) {
            Transaction::create($r);
        }
    }

    private function seedExpenses(): void
    {
        $today = Carbon::today();

        $expenses = [
            ['category' => 'fuel',        'label' => 'Fuel & Transport',     'sub_label' => '14 trips, avg 220L',     'amount' => 12440000, 'percentage' => 44.6, 'bar_color' => '#f0883e', 'icon' => '⛽', 'expense_date' => $today],
            ['category' => 'labour',      'label' => 'Driver & Labour',      'sub_label' => 'Allowances + porter',    'amount' => 8200000,  'percentage' => 29.5, 'bar_color' => '#58a6ff', 'icon' => '👷', 'expense_date' => $today],
            ['category' => 'packaging',   'label' => 'Packaging / Bags',     'sub_label' => 'Gunny & polyprop',       'amount' => 3180000,  'percentage' => 11.4, 'bar_color' => '#3fb950', 'icon' => '📦', 'expense_date' => $today],
            ['category' => 'levies',      'label' => 'Levies & Permits',     'sub_label' => 'Market dues, weigh bridge','amount'=> 1620000, 'percentage' => 5.8,  'bar_color' => '#f85149', 'icon' => '🏛️', 'expense_date' => $today],
            ['category' => 'maintenance', 'label' => 'Vehicle Maintenance',  'sub_label' => 'Servicing + tyres',      'amount' => 2400000,  'percentage' => 8.6,  'bar_color' => '#bc8cff', 'icon' => '🔧', 'expense_date' => $today],
        ];

        foreach ($expenses as $e) {
            Expense::create($e);
        }
    }

    private function seedPriceRecords(): void
    {
        $produces = ProduceType::all()->keyBy('slug');
        $months   = $this->getMonthLabels();

        // Irish Potatoes: base 700, amp 150
        $this->insertPriceHistory($produces['potatoes']->id,      'Sironko',    $months, 700,   150);
        // Groundnuts: base 3800, amp 500
        $this->insertPriceHistory($produces['groundnuts']->id,    'Kapchorwa',  $months, 3800,  500);
        // Beans: base 2600, amp 400
        $this->insertPriceHistory($produces['beans']->id,         'Kumi',       $months, 2600,  400);
        // Simsim: base 7200, amp 1200
        $this->insertPriceHistory($produces['simsim']->id,        'Lira',       $months, 7200,  1200);
        // Maize: base 1200, amp 200
        $this->insertPriceHistory($produces['maize']->id,         'Kasese',     $months, 1200,  200);
        // Sweet Potatoes: base 900, amp 200
        $this->insertPriceHistory($produces['sweet-potatoes']->id,'Masaka',     $months, 900,   200);
    }

    private function insertPriceHistory(int $produceId, string $location, array $months, int $base, int $amp): void
    {
        $seed = $base;
        foreach ($months as $i => ['label' => $label, 'date' => $date]) {
            $price = (int) round($base + sin($i * 0.5) * $amp + ($i * $amp * 0.04) + (rand(0, 100) / 100) * $amp * 0.25);
            PriceRecord::create([
                'produce_type_id' => $produceId,
                'price_per_kg'    => max($price, (int)($base * 0.6)),
                'location'        => $location,
                'period_label'    => $label,
                'recorded_date'   => $date,
            ]);
        }
    }

    private function getMonthLabels(): array
    {
        $labels = ['Jun 24','Jul','Aug','Sep','Oct','Nov','Dec','Jan 25','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec','Jan 26','Feb','Mar','Apr','May 26'];
        $start  = Carbon::create(2024, 6, 1);
        $months = [];
        foreach ($labels as $i => $label) {
            $months[] = ['label' => $label, 'date' => $start->copy()->addMonths($i)->format('Y-m-d')];
        }
        return $months;
    }

    private function seedSyncRecords(): void
    {
        $now = Carbon::now();

        $records = [
            ['trip_id' => 3, 'agent_id' => 3, 'status' => 'failed',  'records_count' => 3,  'offline_hours' => 6,  'synced_at' => null],
            ['trip_id' => 2, 'agent_id' => 2, 'status' => 'pending', 'records_count' => 2,  'offline_hours' => 1,  'synced_at' => null],
            ['trip_id' => 1, 'agent_id' => 1, 'status' => 'pending', 'records_count' => 2,  'offline_hours' => 2,  'synced_at' => null],
            ['trip_id' => 4, 'agent_id' => 4, 'status' => 'synced',  'records_count' => 8,  'offline_hours' => 0,  'synced_at' => $now->copy()->subHours(3)],
            ['trip_id' => 5, 'agent_id' => 5, 'status' => 'synced',  'records_count' => 12, 'offline_hours' => 0,  'synced_at' => $now->copy()->subHours(5)],
        ];

        foreach ($records as $r) {
            SyncRecord::create($r);
        }
    }
}
