<?php

namespace App\Http\Controllers;

use App\Models\PriceRecord;
use App\Models\ProduceType;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceController extends Controller
{
    public function update(Request $request, ProduceType $produceType): JsonResponse
    {
        $data = $request->validate([
            'current_price'    => 'required|integer|min:1',
            'currency'         => 'nullable|string|max:10',
            'change_percent'   => 'required|numeric',
            'signal'           => 'required|in:buy,hold,sell',
            'primary_location' => 'required|string|max:100',
        ]);

        $produceType->update($data);

        // Record new price point
        PriceRecord::create([
            'produce_type_id' => $produceType->id,
            'price_per_kg'    => $data['current_price'],
            'currency'        => $data['currency'] ?? 'UGX',
            'location'        => $data['primary_location'],
            'period_label'    => Carbon::today()->format('M y'),
            'recorded_date'   => Carbon::today(),
        ]);

        return response()->json(['success' => true]);
    }
}
