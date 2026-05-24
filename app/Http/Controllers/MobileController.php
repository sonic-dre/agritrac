<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Expense;
use App\Models\ProduceType;
use App\Models\Transaction;
use App\Models\Trip;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MobileController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $agent = Agent::where('email', $data['email'])->first();

        if (! $agent || ! Hash::check($data['password'], $agent->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (! $agent->is_active) {
            return response()->json(['message' => 'Account is deactivated. Contact HQ.'], 403);
        }

        $agent->tokens()->delete();
        $token = $agent->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'agent' => $this->agentPayload($agent),
        ]);
    }

    public function context(Request $request): JsonResponse
    {
        $agent = $request->user();

        $recentTxns = Transaction::with('produceType')
            ->where('agent_id', $agent->id)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        $trips = $this->agentTrips($agent);

        return response()->json([
            'agent'        => $this->agentPayload($agent),
            'produce'      => ProduceType::orderBy('name')->get(['id', 'name', 'emoji', 'slug', 'current_price', 'change_percent', 'signal', 'primary_location', 'accent_color']),
            'units'        => Unit::orderBy('name')->get(['id', 'name', 'symbol', 'base_kg']),
            'transactions' => $recentTxns->map(fn ($t) => $this->txPayload($t)),
            'trips'        => $trips,
        ]);
    }

    public function trips(Request $request): JsonResponse
    {
        return response()->json($this->agentTrips($request->user()));
    }

    public function prices(): JsonResponse
    {
        return response()->json(
            ProduceType::orderBy('name')->get(['id', 'name', 'emoji', 'slug', 'current_price', 'change_percent', 'signal', 'primary_location'])
        );
    }

    public function transactions(Request $request): JsonResponse
    {
        $txns = Transaction::with('produceType')
            ->where('agent_id', $request->user()->id)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return response()->json($txns->map(fn ($t) => $this->txPayload($t)));
    }

    public function storeTx(Request $request): JsonResponse
    {
        $data = $request->validate([
            'trip_id'          => 'nullable|integer|exists:trips,id',
            'produce_type_id'  => 'nullable|integer|exists:produce_types,id',
            'type'             => 'required|string|max:30',
            'quantity_kg'      => 'nullable|numeric|min:0',
            'unit_id'          => 'nullable|integer|exists:units,id',
            'unit_price'       => 'nullable|integer|min:0',
            'total_amount'     => 'required|integer',
            'currency'         => 'nullable|string|max:10',
            'location'         => 'nullable|string|max:100',
            'category'         => 'nullable|string|max:50',
            'transaction_date' => 'required|date',
            'notes'            => 'nullable|string|max:500',
            'latitude'         => 'nullable|numeric|between:-90,90',
            'longitude'        => 'nullable|numeric|between:-180,180',
            'moisture_content' => 'nullable|numeric|between:0,100',
        ]);

        $tx = Transaction::create(array_merge($data, [
            'agent_id'    => $request->user()->id,
            'sync_status' => 'synced',
            'currency'    => $data['currency'] ?? 'UGX',
        ]));

        // Update trip totals — sales credit revenue; purchases/expenses debit amount_spent
        if (! empty($data['trip_id'])) {
            $trip = Trip::find($data['trip_id']);
            if ($trip) {
                if ($data['type'] === 'sale') {
                    $trip->increment('revenue', abs($data['total_amount']));
                } else {
                    $trip->increment('tonnage_kg', max(0, $data['quantity_kg'] ?? 0));
                    $trip->increment('amount_spent', abs($data['total_amount']));
                }
            }
        }

        return response()->json(['id' => $tx->id, 'saved' => true], 201);
    }

    public function storeExpense(Request $request): JsonResponse
    {
        $data = $request->validate([
            'trip_id'      => 'nullable|integer|exists:trips,id',
            'category'     => 'required|string|max:60',
            'label'        => 'nullable|string|max:100',
            'amount'       => 'required|integer|min:1',
            'expense_date' => 'required|date',
            'notes'        => 'nullable|string|max:500',
            'latitude'     => 'nullable|numeric|between:-90,90',
            'longitude'    => 'nullable|numeric|between:-180,180',
        ]);

        $exp = Expense::create([
            'trip_id'      => $data['trip_id'] ?? null,
            'category'     => $data['category'],
            'label'        => $data['label'] ?? $data['category'],
            'sub_label'    => $data['notes'] ?? null,
            'amount'       => $data['amount'],
            'expense_date' => $data['expense_date'],
            'currency'     => 'UGX',
            'latitude'     => $data['latitude'] ?? null,
            'longitude'    => $data['longitude'] ?? null,
        ]);

        // Update trip spend
        if (! empty($data['trip_id'])) {
            Trip::find($data['trip_id'])?->increment('amount_spent', $data['amount']);
        }

        return response()->json(['id' => $exp->id, 'saved' => true], 201);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function agentTrips(Agent $agent): array
    {
        return Trip::where('agent_id', $agent->id)
            ->whereIn('status', ['departing', 'in_progress', 'returning'])
            ->orderByDesc('start_date')
            ->get()
            ->map(fn ($t) => [
                'id'            => $t->id,
                'region'        => $t->region,
                'status'        => $t->status,
                'status_label'  => $t->status_label,
                'start_date'    => $t->start_date?->format('d M Y'),
                'current_day'   => $t->current_day,
                'total_days'    => $t->total_days,
                'produce_list'  => $t->produce_list ?? [],
                'tonnage_kg'    => $t->tonnage_kg,
                'amount_spent'  => $t->amount_spent,
                'advance_amount'=> $t->advance_amount,
                'currency'      => $t->currency ?? 'UGX',
            ])
            ->values()
            ->all();
    }

    private function agentPayload(Agent $agent): array
    {
        $parts = explode(' ', trim($agent->name));
        $initials = strtoupper(substr($parts[0], 0, 1)) . strtoupper(substr($parts[1] ?? '', 0, 1));

        return [
            'id'            => $agent->id,
            'name'          => $agent->name,
            'initials'      => $initials,
            'region'        => $agent->region,
            'base_location' => $agent->base_location,
            'phone'         => $agent->phone,
            'avatar_color'  => $agent->avatar_color,
        ];
    }

    private function txPayload(Transaction $tx): array
    {
        $produce = $tx->produceType;
        $isExpense = $tx->type === 'expense';
        $amt = abs($tx->total_amount ?? 0);

        $isSale = $tx->type === 'sale';

        return [
            'id'               => $tx->id,
            'type'             => $tx->type,
            'trip_id'          => $tx->trip_id,
            'emoji'            => $isExpense ? '💸' : ($isSale ? '🤝' : ($tx->type === 'advance' ? '💰' : ($produce?->emoji ?? '📦'))),
            'name'             => $isExpense
                ? ($tx->category ?? 'Expense')
                : ($isSale
                    ? (($produce?->name ?? 'Sale') . ($tx->location ? ' → ' . $tx->location : ''))
                    : (($produce?->name ?? 'Purchase') . ($tx->location ? ' — ' . $tx->location : ''))),
            'sub'              => ($tx->quantity_kg ? number_format($tx->quantity_kg) . ' kg' : '') .
                ($tx->location ? ' · ' . $tx->location : '') .
                ($tx->transaction_date ? ' · ' . $tx->transaction_date->format('d M') : ''),
            'amount'           => ($tx->total_amount > 0 ? '+' : '-') . number_format($amt),
            'positive'         => ($tx->total_amount ?? 0) > 0,
            'sync_status'      => $tx->sync_status ?? 'synced',
            'produce_type_id'  => $tx->produce_type_id,
            'quantity_kg'      => $tx->quantity_kg,
            'total_amount'     => $tx->total_amount,
            'transaction_date' => $tx->transaction_date?->format('Y-m-d'),
            'latitude'         => $tx->latitude,
            'longitude'        => $tx->longitude,
            'moisture_content' => $tx->moisture_content,
        ];
    }
}
