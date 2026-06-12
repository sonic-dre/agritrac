<?php

namespace App\Http\Controllers;

use App\Models\LookupValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'group'      => 'required|string|in:grade,payment_method,expense_category',
            'label'      => 'required|string|max:80',
            'value'      => 'required|string|max:80',
            'emoji'      => 'nullable|string|max:10',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $lv = LookupValue::create($data);

        return response()->json(['success' => true, 'item' => $this->fmt($lv)], 201);
    }

    public function update(Request $request, LookupValue $lookupValue): JsonResponse
    {
        $data = $request->validate([
            'label'      => 'required|string|max:80',
            'value'      => 'required|string|max:80',
            'emoji'      => 'nullable|string|max:10',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $lookupValue->update($data);

        return response()->json(['success' => true]);
    }

    public function toggle(LookupValue $lookupValue): JsonResponse
    {
        $lookupValue->update(['is_active' => ! $lookupValue->is_active]);

        return response()->json(['success' => true, 'is_active' => $lookupValue->is_active]);
    }

    public function destroy(LookupValue $lookupValue): JsonResponse
    {
        $lookupValue->delete();

        return response()->json(['success' => true]);
    }

    private function fmt(LookupValue $lv): array
    {
        return [
            'id'         => $lv->id,
            'group'      => $lv->group,
            'label'      => $lv->label,
            'value'      => $lv->value,
            'emoji'      => $lv->emoji,
            'sort_order' => $lv->sort_order,
            'is_active'  => $lv->is_active,
        ];
    }
}
