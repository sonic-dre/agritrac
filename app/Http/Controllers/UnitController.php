<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'    => 'required|string|max:60',
            'symbol'  => 'required|string|max:20',
            'base_kg' => 'nullable|numeric|min:0.001',
        ]);

        $unit = Unit::create($data);

        return response()->json(['success' => true, 'unit' => [
            'id'      => $unit->id,
            'name'    => $unit->name,
            'symbol'  => $unit->symbol,
            'base_kg' => $unit->base_kg,
        ]]);
    }

    public function destroy(Unit $unit): JsonResponse
    {
        $unit->transactions()->update(['unit_id' => null]);
        $unit->delete();

        return response()->json(['success' => true]);
    }
}
