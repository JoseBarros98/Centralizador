<?php

namespace App\Http\Controllers;

use App\Models\MarketingGoalWeek;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketingGoalWeekController extends Controller
{
    public function update(Request $request, MarketingGoalWeek $marketingGoalWeek): JsonResponse
    {
        $allowed = ['weekly_goal', 'week_start', 'week_end', 'meeting_date'];

        $data = $request->validate([
            'field' => 'required|in:' . implode(',', $allowed),
            'value' => 'nullable',
        ]);

        $field = $data['field'];
        $value = $data['value'];

        if (in_array($field, ['week_start', 'week_end', 'meeting_date'])) {
            $value = $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null;
        } else {
            $value = max(0, (int) $value);
        }

        $marketingGoalWeek->update([$field => $value]);

        return response()->json([
            'week' => [
                'id'          => $marketingGoalWeek->id,
                'weekly_goal' => $marketingGoalWeek->weekly_goal,
                'week_start'  => $marketingGoalWeek->week_start?->format('Y-m-d'),
                'week_end'    => $marketingGoalWeek->week_end?->format('Y-m-d'),
                'meeting_date' => $marketingGoalWeek->meeting_date?->format('Y-m-d'),
            ],
        ]);
    }
}
