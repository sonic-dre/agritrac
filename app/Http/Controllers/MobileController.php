<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Expense;
use App\Models\ProduceType;
use App\Models\Transaction;
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

        return response()->json([
            'agent'        => $this->agentPayload($agent),
            'produce'      => ProduceType::orderBy('name')->get(['id', 'name', 'emoji', 'slug', 'current_price', 'change_percent', 'signal', 'primary_location', 'accent_color']),
            'units'        => Unit::orderBy('name')->get(['id', 'name', 'symbol', 'base_kg']),
            'transactions' => $recentTxns->map(fn ($t) => $this->txPayload($t)),
        ]);
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
            'produce_type_id'  => 'nullable|integer|exists:produce_types,id',
            'type'             => 'required|string|max:30',
            'quantity_kg'      => 'nullable|numeric|min:0',
            'unit_id'          => 'nullable|integer|exists:units,id',
            'unit_price'       => 'nullable|integer|min:0',
            'total_amount'     => 'required|integer',
            'location'         => 'nullable|string|max:100',
            'category'         => 'nullable|string|max:50',
            'transaction_date' => 'required|date',
            'notes'            => 'nullable|string|max:500',
        ]);

        $tx = Transaction::create(array_merge($data, [
            'agent_id'    => $request->user()->id,
            'sync_status' => 'synced',
            'currency'    => 'UGX',
        ]));

        return response()->json(['id' => $tx->id, 'saved' => true], 201);
    }

    public function storeExpense(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category'     => 'required|string|max:60',
            'label'        => 'nullable|string|max:100',
            'amount'       => 'required|integer|min:1',
            'expense_date' => 'required|date',
            'notes'        => 'nullable|string|max:500',
        ]);

        $exp = Expense::create([
            'category'     => $data['category'],
            'label'        => $data['label'] ?? $data['category'],
            'sub_label'    => $data['notes'] ?? null,
            'amount'       => $data['amount'],
            'expense_date' => $data['expense_date'],
            'currency'     => 'UGX',
        ]);

        return response()->json(['id' => $exp->id, 'saved' => true], 201);
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

        return [
            'id'               => $tx->id,
            'type'             => $tx->type,
            'emoji'            => $isExpense ? '💸' : ($produce?->emoji ?? '📦'),
            'name'             => $isExpense
                ? ($tx->category ?? 'Expense')
                : (($produce?->name ?? 'Purchase') . ($tx->location ? ' — ' . $tx->location : '')),
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
        ];
    }
}
