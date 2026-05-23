<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'agent_id'        => 'nullable|exists:agents,id',
            'trip_id'         => 'nullable|exists:trips,id',
            'produce_type_id' => 'nullable|exists:produce_types,id',
            'type'            => 'required|in:purchase,expense,advance',
            'quantity_kg'     => 'nullable|numeric|min:0',
            'unit_id'         => 'nullable|exists:units,id',
            'unit_price'      => 'nullable|integer|min:0',
            'currency'        => 'nullable|string|max:10',
            'location'        => 'nullable|string|max:100',
            'category'        => 'nullable|string|max:30',
            'transaction_date'=> 'required|date',
            'sync_status'     => 'required|in:synced,pending,offline',
            'notes'           => 'nullable|string|max:255',
        ]);

        // Auto-calculate total amount
        $data['total_amount'] = match ($data['type']) {
            'purchase' => -1 * (int) (($data['quantity_kg'] ?? 0) * ($data['unit_price'] ?? 0)),
            'advance'  => (int) ($request->input('advance_amount', 0)),
            default    => -1 * (int) ($request->input('expense_amount', 0)),
        };

        $txn = Transaction::create($data);

        // Update trip tonnage and spent if purchase
        if ($data['type'] === 'purchase' && !empty($data['trip_id'])) {
            $trip = Trip::find($data['trip_id']);
            if ($trip) {
                $trip->increment('tonnage_kg', $data['quantity_kg'] ?? 0);
                $trip->increment('amount_spent', abs($data['total_amount']));
            }
        }

        return response()->json(['success' => true, 'id' => $txn->id]);
    }

    public function destroy(Transaction $transaction): JsonResponse
    {
        $transaction->delete();
        return response()->json(['success' => true]);
    }
}
