<?php

namespace App\Http\Controllers;

use App\Models\ProduceType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProduceTypeController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'             => 'required|string|max:80',
            'emoji'            => 'nullable|string|max:10',
            'current_price'    => 'nullable|numeric|min:0',
            'change_percent'   => 'nullable|numeric',
            'signal'           => 'nullable|in:buy,hold,sell',
            'primary_location' => 'nullable|string|max:100',
            'accent_color'     => 'nullable|string|max:20',
        ]);

        $produce = ProduceType::create([
            'name'             => $data['name'],
            'emoji'            => $data['emoji'] ?? '🌿',
            'slug'             => Str::slug($data['name']),
            'current_price'    => $data['current_price']    ?? 0,
            'change_percent'   => $data['change_percent']   ?? 0,
            'signal'           => $data['signal']           ?? 'hold',
            'primary_location' => $data['primary_location'] ?? '',
            'accent_color'     => $data['accent_color']     ?? '#6b7280',
        ]);

        return response()->json(['success' => true, 'produce' => [
            'id'    => $produce->id,
            'name'  => $produce->name,
            'emoji' => $produce->emoji,
            'slug'  => $produce->slug,
        ]]);
    }

    public function update(Request $request, ProduceType $produceType): JsonResponse
    {
        $data = $request->validate([
            'name'             => 'required|string|max:80',
            'emoji'            => 'nullable|string|max:10',
            'current_price'    => 'nullable|numeric|min:0',
            'change_percent'   => 'nullable|numeric',
            'signal'           => 'nullable|in:buy,hold,sell',
            'primary_location' => 'nullable|string|max:100',
            'accent_color'     => 'nullable|string|max:20',
        ]);

        $data['slug']  = Str::slug($data['name']);
        $data['emoji'] = $data['emoji'] ?? '🌿';

        $produceType->update($data);

        return response()->json(['success' => true]);
    }

    public function destroy(ProduceType $produceType): JsonResponse
    {
        $produceType->transactions()->update(['produce_type_id' => null]);
        $produceType->priceRecords()->delete();
        $produceType->delete();

        return response()->json(['success' => true]);
    }
}
