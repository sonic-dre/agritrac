<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'agent_id'                => 'required|exists:agents,id',
            'region'                  => 'required|string|max:100',
            'produce_list'            => 'required|array|min:1',
            'produce_list.*'          => 'string',
            'start_date'              => 'required|date',
            'total_days'              => 'required|integer|min:1|max:30',
            'advance_amount'          => 'required|integer|min:0',
            'payment_type'            => 'required|in:advance,full',
            'negotiated_price_per_kg' => 'nullable|integer|min:1',
            'currency'                => 'nullable|string|max:10',
        ]);

        $trip = Trip::create([
            ...$data,
            'current_day'  => 0,
            'status'       => 'departing',
            'sync_status'  => 'synced',
            'tonnage_kg'   => 0,
            'amount_spent' => 0,
            'revenue'      => 0,
        ]);

        return response()->json(['success' => true, 'id' => $trip->id]);
    }

    public function update(Request $request, Trip $trip): JsonResponse
    {
        $data = $request->validate([
            'region'          => 'sometimes|string|max:100',
            'produce_list'    => 'sometimes|array',
            'produce_list.*'  => 'string',
            'status'          => 'sometimes|in:departing,in_progress,returning,completed',
            'sync_status'     => 'sometimes|in:synced,pending,offline',
            'offline_hours'   => 'sometimes|integer|min:0',
            'unsynced_records'=> 'sometimes|integer|min:0',
            'current_day'     => 'sometimes|integer|min:0',
            'total_days'      => 'sometimes|integer|min:1',
            'tonnage_kg'      => 'sometimes|numeric|min:0',
            'amount_spent'    => 'sometimes|integer|min:0',
            'advance_amount'  => 'sometimes|integer|min:0',
        ]);

        $trip->update($data);

        return response()->json(['success' => true]);
    }

    public function destroy(Trip $trip): JsonResponse
    {
        $trip->transactions()->delete();
        $trip->expenses()->delete();
        $trip->syncRecords()->delete();
        $trip->delete();

        return response()->json(['success' => true]);
    }
}
