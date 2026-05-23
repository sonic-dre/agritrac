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
            'name'  => 'required|string|max:80',
            'emoji' => 'nullable|string|max:10',
        ]);

        $produce = ProduceType::create([
            'name'             => $data['name'],
            'emoji'            => $data['emoji'] ?? '🌿',
            'slug'             => Str::slug($data['name']),
            'current_price'    => 0,
            'change_percent'   => 0,
            'signal'           => 'hold',
            'primary_location' => '',
            'accent_color'     => '#6b7280',
        ]);

        return response()->json(['success' => true, 'produce' => [
            'id'    => $produce->id,
            'name'  => $produce->name,
            'emoji' => $produce->emoji,
            'slug'  => $produce->slug,
        ]]);
    }
}
