<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearDemoData extends Command
{
    protected $signature   = 'app:clear-demo';
    protected $description = 'Truncate all transactional demo data, keeping users, produce types, units and currencies';

    public function handle(): int
    {
        if (! $this->confirm('This will delete ALL trips, transactions, expenses, agents, price records and sync records. Continue?')) {
            $this->info('Aborted.');
            return 0;
        }

        DB::statement('PRAGMA foreign_keys = OFF');

        foreach ([
            'sync_records',
            'transactions',
            'expenses',
            'trips',
            'price_records',
            'agents',
        ] as $table) {
            DB::table($table)->truncate();
            $this->line("  cleared: {$table}");
        }

        DB::statement('PRAGMA foreign_keys = ON');

        $this->info('Done. All demo data removed.');
        return 0;
    }
}
