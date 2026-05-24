<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Expense;
use App\Models\PriceRecord;
use App\Models\ProduceType;
use App\Models\SyncRecord;
use App\Models\Transaction;
use App\Models\Trip;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index', [
            'syncCount'    => SyncRecord::where('status', '!=', 'synced')->count(),
            'agents'       => Agent::orderBy('name')->get(['id', 'name', 'initials']),
            'produceTypes' => ProduceType::orderBy('name')->get(['id', 'name', 'emoji', 'slug']),
            'units'        => Unit::orderBy('name')->get(['id', 'name', 'symbol', 'base_kg']),
        ]);
    }

    // ─── API endpoints called by JavaScript ───────────────────────────────────

    public function overview(): JsonResponse
    {
        $trips = Trip::where('status', '!=', 'completed')->with('agent')->get();

        // Last 10 completed trips for chart
        $completed = Trip::where('status', 'completed')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'kpis'      => $this->kpis(),
            'mainChart' => [
                'labels'  => $completed->map(fn ($t, $i) => 'T' . ($i + 1))->values(),
                'revenue' => $completed->map(fn ($t) => round($t->revenue / 1_000_000, 1)),
                'cost'    => $completed->map(fn ($t) => round($t->amount_spent / 1_000_000, 1)),
                'profit'  => $completed->map(fn ($t) => round(($t->revenue - $t->amount_spent) / 1_000_000, 1)),
            ],
            'expDonut' => $this->expenseDonut(),
            'routes'   => $trips->map(fn ($t) => [
                'initials'   => $t->agent->initials,
                'region'     => $t->region,
                'produce'    => $t->produce_string,
                'agent'      => $t->agent->name,
                'status'     => $t->status,
                'sync'       => $t->sync_status,
                'day'        => $t->current_day,
                'total_days' => $t->total_days,
                'tonnage'    => number_format($t->tonnage_kg / 1000, 1) . 'T',
                'spent'      => number_format($t->amount_spent / 1_000_000, 1) . 'M',
                'unsynced'   => $t->unsynced_records,
                'offline_h'  => $t->offline_hours,
            ]),
            'prices'   => ProduceType::all()->map(fn ($p) => [
                'id'            => $p->id,
                'name'          => $p->name,
                'emoji'         => $p->emoji,
                'slug'          => $p->slug,
                'location'      => $p->primary_location,
                'price'         => number_format($p->current_price),
                'price_raw'     => $p->current_price,
                'change'        => $p->change_percent,
                'signal'        => $p->signal,
                'signal_label'  => $p->signal_label,
                'signal_class'  => $p->signal_class,
                'dot_color'     => $p->accent_color,
            ]),
        ]);
    }

    public function trips(): JsonResponse
    {
        $active = Trip::where('status', '!=', 'completed')
            ->with('agent')
            ->get();

        // Per-agent breakdown for the metrics strip
        $agentStats = $active->groupBy('agent_id')->map(function ($trips) {
            $a = $trips->first()->agent;
            return [
                'id'      => $a->id,
                'name'    => $a->name,
                'color'   => $a->avatar_color ?? '#58a6ff',
                'trips'   => $trips->count(),
                'tonnage' => round($trips->sum('tonnage_kg') / 1000, 2),
                'spent'   => number_format(round($trips->sum('amount_spent') / 1_000_000, 1)) . 'M',
            ];
        })->values();

        return response()->json([
            'stats' => [
                'active'       => $active->count(),
                'tonnage'      => round($active->sum('tonnage_kg') / 1000, 1),
                'capital_out'  => round($active->sum('advance_amount') / 1_000_000, 1),
                'next_arrival' => $active->where('status', 'returning')->first()?->agent->name ?? 'None',
            ],
            'agent_stats' => $agentStats,
            'table' => $active->map(fn ($t) => [
                'id'                      => $t->id,
                'agent_id'                => $t->agent_id,
                'agent'                   => $t->agent->name,
                'agent_color'             => $t->agent->avatar_color ?? '#58a6ff',
                'region'                  => $t->region,
                'produce'                 => $t->produce_string,
                'tonnage'                 => number_format($t->tonnage_kg / 1000, 1),
                'tonnage_raw'             => $t->tonnage_kg,
                'spent'                   => number_format($t->amount_spent),
                'spent_raw'               => $t->amount_spent,
                'advance_raw'             => $t->advance_amount,
                'advance_fmt'             => number_format($t->advance_amount),
                'payment_type'            => $t->payment_type ?? 'advance',
                'negotiated_price_per_kg' => $t->negotiated_price_per_kg,
                'neg_price_fmt'           => $t->negotiated_price_per_kg ? number_format($t->negotiated_price_per_kg) . '/kg' : null,
                'currency'                => $t->currency ?? 'UGX',
                'day'                     => $t->current_day . '/' . $t->total_days,
                'sync_badge'              => $t->sync_badge,
                'sync_label'              => $t->sync_label,
                'sync_status'             => $t->sync_status,
                'offline_h'               => $t->offline_hours,
                'status_label'            => $t->status_label,
                'status'                  => $t->status,
            ]),
        ]);
    }

    public function fieldMap(): JsonResponse
    {
        $points = Transaction::with(['agent', 'produceType', 'trip'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(500)
            ->get()
            ->map(fn ($t) => [
                'id'          => $t->id,
                'lat'         => (float) $t->latitude,
                'lng'         => (float) $t->longitude,
                'agent_id'    => $t->agent_id,
                'agent_name'  => $t->agent?->name ?? '—',
                'agent_color' => $t->agent?->avatar_color ?? '#3fb950',
                'produce'     => $t->produceType?->name ?? ($t->type === 'expense' ? ($t->category ?? 'Expense') : 'Transaction'),
                'emoji'       => $t->produceType?->emoji ?? ($t->type === 'expense' ? '💸' : ($t->type === 'advance' ? '💰' : '📦')),
                'type'        => $t->type,
                'type_label'  => match($t->type) { 'purchase' => 'Purchase', 'sale' => 'Sale', 'expense' => 'Expense', 'advance' => 'Advance', default => ucfirst($t->type) },
                'qty_kg'      => $t->quantity_kg,
                'unit_price'  => $t->unit_price,
                'amount'      => $t->total_amount,
                'currency'    => $t->currency ?? 'UGX',
                'location'    => $t->location,
                'date'        => $t->transaction_date?->format('d M Y'),
                'moisture'    => $t->moisture_content,
                'sync_status' => $t->sync_status ?? 'synced',
                'notes'       => $t->notes,
                'trip_id'     => $t->trip_id,
                'trip_region' => $t->trip?->region,
                'category'    => $t->category,
            ]);

        $agentIds = $points->pluck('agent_id')->unique()->filter();
        $agents = Agent::whereIn('id', $agentIds)
            ->get(['id', 'name', 'avatar_color'])
            ->map(fn ($a) => [
                'id'    => $a->id,
                'name'  => $a->name,
                'color' => $a->avatar_color ?? '#3fb950',
            ]);

        return response()->json([
            'count'  => $points->count(),
            'points' => $points->values(),
            'agents' => $agents->values(),
        ]);
    }

    public function prices(): JsonResponse
    {
        $produces = ProduceType::with(['priceRecords' => fn ($q) => $q->orderBy('recorded_date')])->get();

        $charts = [];
        foreach ($produces as $p) {
            $charts[$p->slug] = [
                'name'   => $p->name,
                'emoji'  => $p->emoji,
                'color'  => $p->accent_color,
                'price'  => number_format($p->current_price),
                'change' => $p->change_percent,
                'labels' => $p->priceRecords->pluck('period_label'),
                'data'   => $p->priceRecords->pluck('price_per_kg'),
            ];
        }

        return response()->json([
            'stats'  => [
                'best_buy'    => 'Potatoes',
                'avoid'       => 'Gnuts',
                'best_margin' => 'Simsim',
                'last_sync'   => '2h ago',
            ],
            'charts' => $charts,
            'prices' => $produces->map(fn ($p) => [
                'id'           => $p->id,
                'name'         => $p->name,
                'emoji'        => $p->emoji,
                'location'     => $p->primary_location,
                'price'        => number_format($p->current_price),
                'price_raw'    => $p->current_price,
                'currency'     => $p->priceRecords->last()?->currency ?? 'UGX',
                'change'       => $p->change_percent,
                'signal'       => $p->signal,
                'signal_label' => $p->signal_label,
                'signal_class' => $p->signal_class,
                'dot_color'    => $p->accent_color,
            ]),
        ]);
    }

    public function forecast(): JsonResponse
    {
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        return response()->json([
            'stats' => [
                'model'       => 'Seasonal ARIMA',
                'confidence'  => '84%',
                'last_trained'=> 'May 10',
                'data_points' => '8,640',
            ],
            'chart' => [
                'labels' => $days,
                'datasets' => [
                    ['label' => 'Potatoes', 'color' => '#3fb950', 'data' => [850, 868, 875, 890, 912, 920, 940]],
                    ['label' => 'Gnuts',    'color' => '#f85149', 'data' => [4200, 4180, 4150, 4120, 4090, 4060, 4010]],
                    ['label' => 'Beans',    'color' => '#58a6ff', 'data' => [3100, 3120, 3150, 3180, 3200, 3230, 3250]],
                    ['label' => 'Simsim',   'color' => '#d29922', 'data' => [8500, 8560, 8600, 8650, 8700, 8750, 8820]],
                ],
            ],
            'cards' => [
                ['emoji' => '🥔', 'name' => 'Irish Potatoes',  'trend' => '↑ +8–12%',  'color' => '#3fb950', 'text' => 'Harvest season ending in Elgon. Supply tightening expected.',     'signal' => 'Strong buy signal.',      'signal_color' => '#3fb950'],
                ['emoji' => '🥜', 'name' => 'Groundnuts',      'trend' => '↓ –3–5%',   'color' => '#f85149', 'text' => 'New crop entering Kapchorwa. Supply surplus likely.',             'signal' => 'Hold or reduce volumes.', 'signal_color' => '#d29922'],
                ['emoji' => '🫘', 'name' => 'Beans',           'trend' => '↑ +4–7%',   'color' => '#3fb950', 'text' => 'High Kampala restaurant demand. Kumi stocks low.',               'signal' => 'Buy now.',                'signal_color' => '#3fb950'],
                ['emoji' => '🌽', 'name' => 'Maize (Dry)',     'trend' => '→ Stable',   'color' => '#6e7681', 'text' => 'Government buffer stock stabilising prices.',                    'signal' => 'Normal volumes recommended.','signal_color' => '#6e7681'],
                ['emoji' => '🌿', 'name' => 'Simsim',          'trend' => '↑ +3–6%',   'color' => '#3fb950', 'text' => 'Export demand from Sudan corridor active.',                      'signal' => 'Buy and hold short-term.', 'signal_color' => '#3fb950'],
                ['emoji' => '🍠', 'name' => 'Sweet Potatoes',  'trend' => '↓ –4–8%',   'color' => '#f85149', 'text' => 'Peak harvest in Masaka. Oversupply likely.',                    'signal' => 'Avoid or sell fast.',     'signal_color' => '#f85149'],
            ],
        ]);
    }

    public function accounting(): JsonResponse
    {
        $months   = ['Jan', 'Feb', 'Mar', 'Apr', 'May'];
        $revenues = [280, 310, 295, 320, 342.8];
        $costs    = [210, 230, 220, 242, 253.4];
        $profits  = array_map(fn ($r, $c) => round($r - $c, 1), $revenues, $costs);

        // Trip profit chart
        $completed = Trip::where('status', 'completed')->limit(22)->get();
        $tpLabels  = $completed->map((fn ($t, $i) => 'T' . ($i + 1)))->values();
        $tpData    = $completed->map(fn ($t) => round(($t->revenue - $t->amount_spent) / 1_000_000, 1));

        return response()->json([
            'stats' => [
                'revenue'   => '342.8M',
                'costs'     => '253.4M',
                'profit'    => '89.4M',
                'advances'  => '84.2M',
            ],
            'plChart' => [
                'labels'  => $months,
                'revenue' => $revenues,
                'cost'    => $costs,
                'profit'  => $profits,
            ],
            'prodChart' => [
                'labels' => ['Potatoes', 'Gnuts', 'Beans', 'Maize', 'Simsim', 'Other'],
                'data'   => [38, 22, 18, 12, 7, 3],
                'colors' => ['rgba(210,153,34,.8)', 'rgba(63,185,80,.8)', 'rgba(88,166,255,.8)', 'rgba(240,136,62,.8)', 'rgba(188,140,255,.8)', 'rgba(110,118,129,.6)'],
            ],
            'tpChart' => [
                'labels' => $tpLabels,
                'data'   => $tpData,
            ],
        ]);
    }

    public function expenses(): JsonResponse
    {
        $expenses = Expense::orderByDesc('percentage')->get();
        $months   = ['Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May'];

        return response()->json([
            'stats' => [
                'total'  => '27.8M',
                'fuel'   => '12.4M',
                'labour' => '8.2M',
                'other'  => '7.2M',
            ],
            'breakdown' => $expenses->map(fn ($e) => [
                'id'         => $e->id,
                'icon'       => $e->icon,
                'label'      => $e->label,
                'sub_label'  => $e->sub_label,
                'category'   => $e->category,
                'amount'     => number_format($e->amount),
                'amount_raw' => $e->amount,
                'currency'   => $e->currency ?? 'UGX',
                'date'       => $e->expense_date->format('Y-m-d'),
                'percentage' => $e->percentage,
                'bar_color'  => $e->bar_color,
                'bar_width'  => (int) ($e->percentage * 2.0),
            ]),
            'trendChart' => [
                'labels' => $months,
                'fuel'   => [9.2, 10.1, 9.8, 11.2, 10.4, 11.8, 10.9, 12.1, 11.4, 12.8, 11.9, 12.44],
                'labour' => [6.1, 6.4, 6.2, 7.0, 6.8, 7.2, 7.0, 7.5, 7.2, 7.8, 7.6, 8.2],
            ],
        ]);
    }

    public function history(): JsonResponse
    {
        $txns = Transaction::with(['agent', 'produceType', 'trip', 'unit'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return response()->json([
            'transactions' => $txns->map(fn ($t, $i) => [
                'id'           => $t->id,
                'seq'          => str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'date'         => $t->transaction_date->format('d M'),
                'agent'        => $t->agent ? $this->shortName($t->agent->name) : 'HQ',
                'item'         => $t->produceType
                    ? ($t->produceType->emoji . ' ' . $t->produceType->name)
                    : ($t->type === 'sale' ? 'Sale' : ($t->category ? ucfirst($t->category) : 'Advance')),
                'item_emoji'   => $t->type === 'sale' ? '🤝' : ($t->type === 'advance' ? '💵' : ($t->category === 'fuel' ? '⛽' : ($t->category === 'labour' ? '👷' : ''))),
                'location'     => $t->location ?? '—',
                'quantity'     => $t->quantity_kg ? number_format($t->quantity_kg) . ($t->unit ? ' ' . $t->unit->symbol : ' kg') : '—',
                'unit_price'   => $t->unit_price ? number_format($t->unit_price) : '—',
                'currency'     => $t->currency ?? 'UGX',
                'total'        => number_format(abs($t->total_amount)),
                'is_positive'  => $t->total_amount > 0,
                'type'         => ucfirst($t->type),
                'sync_badge'   => $t->sync_badge,
                'sync_label'   => $t->sync_label,
            ])->values(),
        ]);
    }

    public function sync(): JsonResponse
    {
        $records = SyncRecord::with(['agent', 'trip'])->orderBy('status')->get();

        return response()->json([
            'stats' => [
                'pending'    => $records->whereIn('status', ['pending', 'failed'])->count(),
                'offline'    => $records->where('status', 'failed')->count(),
                'last_sync'  => '2h 14m',
                'total_synced'=> 142,
            ],
            'queue' => $records->map(fn ($r) => [
                'agent'       => $r->agent->name,
                'region'      => $r->trip->region,
                'records'     => $r->records_count,
                'status'      => $r->status,
                'dot_class'   => $r->dot_class,
                'time_label'  => $r->status === 'synced'
                    ? $r->synced_at?->diffForHumans() ?? '—'
                    : ($r->status === 'failed' ? 'Offline ' . $r->offline_hours . 'h' : 'Pending ' . $r->offline_hours . 'h'),
                'description' => $r->records_count . ' records · ' . implode(' & ', (array) ($r->trip->produce_list ?? [])),
                'opacity'     => $r->status === 'synced' ? 0.55 : 1.0,
            ]),
            'chart' => [
                'labels'  => ['06:00', '07:00', '08:00', '09:00', '10:00', '11:00', '12:00'],
                'synced'  => [12, 8, 15, 4, 0, 0, 0],
                'failed'  => [0, 0, 2, 1, 3, 2, 2],
            ],
        ]);
    }

    public function stock(): JsonResponse
    {
        $produces = ProduceType::all();

        $stockData = [
            ['name' => 'Irish Potatoes',  'emoji' => '🥔', 'in_transit' => 18.4, 'kampala' => 6.2,  'est_value' => 29520000, 'status' => 'Selling', 'badge' => 'sp-pe'],
            ['name' => 'Groundnuts',      'emoji' => '🥜', 'in_transit' => 3.8,  'kampala' => 1.4,  'est_value' => 28600000, 'status' => 'Good',    'badge' => 'sp-sy'],
            ['name' => 'Beans (K132)',    'emoji' => '🫘', 'in_transit' => 6.4,  'kampala' => 2.8,  'est_value' => 35880000, 'status' => 'Good',    'badge' => 'sp-sy'],
            ['name' => 'Maize Dry',       'emoji' => '🌽', 'in_transit' => 6.8,  'kampala' => 3.4,  'est_value' => 19380000, 'status' => 'Good',    'badge' => 'sp-sy'],
            ['name' => 'Simsim',          'emoji' => '🌿', 'in_transit' => 1.8,  'kampala' => 0.4,  'est_value' => 22440000, 'status' => 'Low',     'badge' => 'sp-pe'],
            ['name' => 'Sweet Potatoes',  'emoji' => '🍠', 'in_transit' => 1.2,  'kampala' => 0.0,  'est_value' => 1320000,  'status' => 'Sell Fast','badge'=> 'sp-of'],
        ];

        return response()->json([
            'stats' => [
                'in_transit'    => '38.4 T',
                'kampala_stock' => '14.2 T',
                'bags_used'     => '284',
                'next_arrival'  => 'Today 6pm',
            ],
            'table' => array_map(fn ($row) => [
                ...$row,
                'total'     => round($row['in_transit'] + $row['kampala'], 1),
                'est_value' => number_format($row['est_value']),
                'value_positive' => $row['est_value'] > 5000000,
            ], $stockData),
        ]);
    }

    public function produceUnits(): JsonResponse
    {
        $produces = ProduceType::withCount(['transactions', 'priceRecords'])
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => [
                'id'               => $p->id,
                'name'             => $p->name,
                'emoji'            => $p->emoji,
                'slug'             => $p->slug,
                'current_price'    => $p->current_price,
                'price_fmt'        => number_format($p->current_price),
                'change_percent'   => $p->change_percent,
                'signal'           => $p->signal,
                'signal_label'     => $p->signal_label,
                'signal_class'     => $p->signal_class,
                'primary_location' => $p->primary_location,
                'accent_color'     => $p->accent_color,
                'txn_count'        => $p->transactions_count,
                'price_records'    => $p->price_records_count,
            ]);

        $units = Unit::withCount('transactions')
            ->orderBy('name')
            ->get()
            ->map(fn ($u) => [
                'id'        => $u->id,
                'name'      => $u->name,
                'symbol'    => $u->symbol,
                'base_kg'   => $u->base_kg,
                'txn_count' => $u->transactions_count,
            ]);

        return response()->json([
            'stats' => [
                'produce_count' => $produces->count(),
                'unit_count'    => $units->count(),
                'buy_signals'   => $produces->where('signal', 'buy')->count(),
                'sell_signals'  => $produces->where('signal', 'sell')->count(),
            ],
            'produces' => $produces->values(),
            'units'    => $units->values(),
        ]);
    }

    public function mobileAgents(): JsonResponse
    {
        if (!Auth::user()?->isManager()) {
            abort(403);
        }

        $agents = Agent::withCount(['trips', 'transactions'])
            ->orderBy('name')
            ->get()
            ->map(fn ($a) => [
                'id'            => $a->id,
                'name'          => $a->name,
                'initials'      => $a->initials,
                'region'        => $a->region,
                'base_location' => $a->base_location ?? '—',
                'phone'         => $a->phone ?? '—',
                'email'         => $a->email ?? '',
                'avatar_color'  => $a->avatar_color,
                'is_active'     => $a->is_active,
                'has_login'     => !empty($a->email) && !empty($a->password),
                'trips_count'   => $a->trips_count,
                'txn_count'     => $a->transactions_count,
                'created'       => $a->created_at->format('d M Y'),
            ]);

        $active = $agents->where('is_active', true)->count();

        return response()->json([
            'stats' => [
                'total'      => $agents->count(),
                'active'     => $active,
                'inactive'   => $agents->count() - $active,
                'with_login' => $agents->where('has_login', true)->count(),
            ],
            'agents' => $agents->values(),
        ]);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function kpis(): array
    {
        $activeTripCount = Trip::whereNotIn('status', ['completed'])->count();
        $pendingSync     = SyncRecord::where('status', '!=', 'synced')->count();
        $revenue         = Trip::where('status', 'completed')->sum('revenue');
        $profit          = Trip::where('status', 'completed')->sum('revenue') - Trip::where('status', 'completed')->sum('amount_spent');

        return [
            'revenue_mtd'    => '342.8M',
            'tonnage_bought' => '184.2T',
            'net_profit'     => '89.4M',
            'active_trips'   => $activeTripCount,
            'pending_sync'   => $pendingSync,
        ];
    }

    private function expenseDonut(): array
    {
        $expenses = Expense::orderByDesc('amount')->get();
        return [
            'labels' => $expenses->pluck('label'),
            'data'   => $expenses->pluck('amount'),
            'colors' => ['rgba(240,136,62,.8)', 'rgba(88,166,255,.8)', 'rgba(63,185,80,.8)', 'rgba(248,81,73,.7)', 'rgba(188,140,255,.8)'],
        ];
    }

    private function shortName(string $name): string
    {
        $parts = explode(' ', $name);
        return $parts[0][0] . '. ' . ($parts[1] ?? '');
    }
}
