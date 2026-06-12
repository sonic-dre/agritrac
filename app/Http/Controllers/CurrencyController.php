<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code'       => 'required|string|max:10|unique:currencies,code',
            'name'       => 'required|string|max:100',
            'symbol'     => 'required|string|max:10',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['code']       = strtoupper(trim($data['code']));
        $data['sort_order'] = $data['sort_order'] ?? Currency::max('sort_order') + 1;

        $currency = Currency::create($data);

        return response()->json(['success' => true, 'currency' => $this->payload($currency)]);
    }

    public function update(Request $request, Currency $currency): JsonResponse
    {
        $data = $request->validate([
            'code'       => 'required|string|max:10|unique:currencies,code,' . $currency->id,
            'name'       => 'required|string|max:100',
            'symbol'     => 'required|string|max:10',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $currency->update($data);

        return response()->json(['success' => true]);
    }

    public function toggle(Currency $currency): JsonResponse
    {
        $currency->update(['is_active' => ! $currency->is_active]);
        return response()->json(['success' => true, 'is_active' => $currency->is_active]);
    }

    public function destroy(Currency $currency): JsonResponse
    {
        $currency->delete();
        return response()->json(['success' => true]);
    }

    private function payload(Currency $c): array
    {
        return [
            'id'         => $c->id,
            'code'       => $c->code,
            'name'       => $c->name,
            'symbol'     => $c->symbol,
            'is_active'  => $c->is_active,
            'sort_order' => $c->sort_order,
        ];
    }
}
