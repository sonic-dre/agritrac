<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category'    => 'required|in:fuel,labour,packaging,levies,maintenance,other',
            'label'       => 'required|string|max:100',
            'sub_label'   => 'nullable|string|max:100',
            'amount'      => 'required|integer|min:1',
            'currency'    => 'nullable|string|max:10',
            'expense_date'=> 'required|date',
            'trip_id'     => 'nullable|exists:trips,id',
        ]);

        $meta = self::categoryMeta($data['category']);

        Expense::create([
            ...$data,
            'bar_color'  => $meta['bar_color'],
            'icon'       => $meta['icon'],
            'percentage' => 0,
        ]);

        $this->recalcPercentages();

        return response()->json(['success' => true]);
    }

    public function update(Request $request, Expense $expense): JsonResponse
    {
        $data = $request->validate([
            'category'    => 'sometimes|in:fuel,labour,packaging,levies,maintenance,other',
            'label'       => 'sometimes|string|max:100',
            'sub_label'   => 'nullable|string|max:100',
            'amount'      => 'sometimes|integer|min:1',
            'currency'    => 'nullable|string|max:10',
            'expense_date'=> 'sometimes|date',
        ]);

        if (isset($data['category'])) {
            $meta = self::categoryMeta($data['category']);
            $data['bar_color'] = $meta['bar_color'];
            $data['icon']      = $meta['icon'];
        }

        $expense->update($data);
        $this->recalcPercentages();

        return response()->json(['success' => true]);
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $expense->delete();
        $this->recalcPercentages();
        return response()->json(['success' => true]);
    }

    private function recalcPercentages(): void
    {
        $total = Expense::sum('amount');
        if ($total > 0) {
            Expense::all()->each(fn ($e) => $e->update([
                'percentage' => round(($e->amount / $total) * 100, 1),
            ]));
        }
    }

    public static function categoryMeta(string $cat): array
    {
        return match ($cat) {
            'fuel'        => ['icon' => '⛽', 'bar_color' => '#f0883e'],
            'labour'      => ['icon' => '👷', 'bar_color' => '#58a6ff'],
            'packaging'   => ['icon' => '📦', 'bar_color' => '#3fb950'],
            'levies'      => ['icon' => '🏛️', 'bar_color' => '#f85149'],
            'maintenance' => ['icon' => '🔧', 'bar_color' => '#bc8cff'],
            default       => ['icon' => '💰', 'bar_color' => '#6e7681'],
        };
    }
}
