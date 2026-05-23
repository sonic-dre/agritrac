<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ProduceType;
use App\Models\SyncRecord;
use App\Models\Transaction;
use App\Models\Trip;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function download(string $page): StreamedResponse
    {
        [$filename, $headers, $rows] = match ($page) {
            'tr'    => $this->tripsData(),
            'pr'    => $this->pricesData(),
            'ac'    => $this->accountingData(),
            'ex'    => $this->expensesData(),
            'hi'    => $this->historyData(),
            'sy'    => $this->syncData(),
            'st'    => $this->stockData(),
            default => $this->overviewData(),
        };

        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function tripsData(): array
    {
        $trips = Trip::with('agent')->whereNotIn('status', ['completed'])->get();
        $headers = ['Agent', 'Region', 'Produce', 'Tonnage (T)', 'Spent (UGX)', 'Day', 'Sync', 'Status'];
        $rows = $trips->map(fn ($t) => [
            $t->agent->name,
            $t->region,
            $t->produce_string,
            number_format($t->tonnage_kg / 1000, 1),
            number_format($t->amount_spent),
            $t->current_day . '/' . $t->total_days,
            $t->sync_label,
            $t->status_label,
        ])->toArray();
        return ['agritrac_trips_' . date('Ymd') . '.csv', $headers, $rows];
    }

    private function pricesData(): array
    {
        $produces = ProduceType::all();
        $headers  = ['Produce', 'Location', 'Price/kg (UGX)', 'Change %', 'Signal'];
        $rows = $produces->map(fn ($p) => [
            $p->emoji . ' ' . $p->name,
            $p->primary_location,
            number_format($p->current_price),
            $p->change_percent . '%',
            strtoupper($p->signal),
        ])->toArray();
        return ['agritrac_prices_' . date('Ymd') . '.csv', $headers, $rows];
    }

    private function accountingData(): array
    {
        $months   = ['Jan', 'Feb', 'Mar', 'Apr', 'May'];
        $revenues = [280, 310, 295, 320, 342.8];
        $costs    = [210, 230, 220, 242, 253.4];
        $headers  = ['Month', 'Revenue (M UGX)', 'Cost (M UGX)', 'Profit (M UGX)'];
        $rows = array_map(fn ($m, $r, $c) => [$m, $r, $c, round($r - $c, 1)], $months, $revenues, $costs);
        return ['agritrac_accounting_' . date('Ymd') . '.csv', $headers, $rows];
    }

    private function expensesData(): array
    {
        $expenses = Expense::orderByDesc('amount')->get();
        $headers  = ['Category', 'Label', 'Sub Label', 'Amount (UGX)', 'Percentage', 'Date'];
        $rows = $expenses->map(fn ($e) => [
            $e->category,
            $e->label,
            $e->sub_label,
            number_format($e->amount),
            $e->percentage . '%',
            $e->expense_date->format('Y-m-d'),
        ])->toArray();
        return ['agritrac_expenses_' . date('Ymd') . '.csv', $headers, $rows];
    }

    private function historyData(): array
    {
        $txns = Transaction::with(['agent', 'produceType'])->orderByDesc('transaction_date')->get();
        $headers = ['Date', 'Agent', 'Item', 'Location', 'Qty (kg)', 'Unit Price', 'Total (UGX)', 'Type', 'Sync'];
        $rows = $txns->map(fn ($t) => [
            $t->transaction_date->format('Y-m-d'),
            $t->agent?->name ?? 'HQ',
            $t->produceType ? $t->produceType->name : ($t->category ?? $t->type),
            $t->location ?? '—',
            $t->quantity_kg ?? '—',
            $t->unit_price ? number_format($t->unit_price) : '—',
            number_format($t->total_amount),
            ucfirst($t->type),
            $t->sync_status,
        ])->toArray();
        return ['agritrac_history_' . date('Ymd') . '.csv', $headers, $rows];
    }

    private function syncData(): array
    {
        $records = SyncRecord::with(['agent', 'trip'])->get();
        $headers = ['Agent', 'Region', 'Status', 'Records', 'Offline Hours', 'Synced At'];
        $rows = $records->map(fn ($r) => [
            $r->agent->name,
            $r->trip->region,
            $r->status,
            $r->records_count,
            $r->offline_hours,
            $r->synced_at?->format('Y-m-d H:i') ?? '—',
        ])->toArray();
        return ['agritrac_sync_' . date('Ymd') . '.csv', $headers, $rows];
    }

    private function stockData(): array
    {
        $headers = ['Produce', 'In Transit (T)', 'Kampala Stock (T)', 'Total (T)', 'Est. Value (UGX)', 'Status'];
        $rows = [
            ['Irish Potatoes', 18.4, 6.2, 24.6, '29,520,000', 'Selling'],
            ['Groundnuts',     3.8,  1.4, 5.2,  '28,600,000', 'Good'],
            ['Beans (K132)',   6.4,  2.8, 9.2,  '35,880,000', 'Good'],
            ['Maize Dry',      6.8,  3.4, 10.2, '19,380,000', 'Good'],
            ['Simsim',         1.8,  0.4, 2.2,  '22,440,000', 'Low'],
            ['Sweet Potatoes', 1.2,  0.0, 1.2,  '1,320,000',  'Sell Fast'],
        ];
        return ['agritrac_stock_' . date('Ymd') . '.csv', $headers, $rows];
    }

    private function overviewData(): array
    {
        $headers = ['Metric', 'Value'];
        $rows = [
            ['Revenue MTD',    'UGX 342.8M'],
            ['Tonnage Bought', '184.2 T'],
            ['Net Profit MTD', 'UGX 89.4M'],
            ['Active Trips',   Trip::whereNotIn('status', ['completed'])->count()],
            ['Pending Sync',   SyncRecord::where('status', '!=', 'synced')->count()],
        ];
        return ['agritrac_overview_' . date('Ymd') . '.csv', $headers, $rows];
    }
}
