<?php

namespace App\Http\Controllers;

use App\Models\MarketingCourseType;
use App\Models\MarketingGoalWeek;
use App\Models\MarketingGoalWeekEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketingGoalWeekEntryController extends Controller
{
    public function update(Request $request, int $weekId, int $courseTypeId): JsonResponse
    {
        $data = $request->validate([
            'field' => 'required|in:completo,adelanto,completando',
            'value' => 'required|integer|min:0',
        ]);

        $entry = MarketingGoalWeekEntry::firstOrCreate(
            ['week_id' => $weekId, 'course_type_id' => $courseTypeId],
            ['completo' => 0, 'adelanto' => 0, 'completando' => 0]
        );

        $entry->update([$data['field'] => max(0, (int) $data['value'])]);

        $week = MarketingGoalWeek::find($weekId);
        $goal = $week->goal()->with('weeks.entries')->first();

        $courseTypes = MarketingCourseType::where('active', true)->get();
        $typeTotals  = [];
        foreach ($courseTypes as $ct) {
            $typeTotals[$ct->id] = [
                'completo'    => $goal->totalForType($ct->id, 'completo'),
                'adelanto'    => $goal->totalForType($ct->id, 'adelanto'),
                'completando' => $goal->totalForType($ct->id, 'completando'),
            ];
        }

        return response()->json([
            'entry' => [
                'id'             => $entry->id,
                'week_id'        => $entry->week_id,
                'course_type_id' => $entry->course_type_id,
                'completo'       => $entry->completo,
                'adelanto'       => $entry->adelanto,
                'completando'    => $entry->completando,
            ],
            'goal' => [
                'id'             => $goal->id,
                'national_total' => $goal->nationalTotal(),
                'diferencia'     => $goal->diferencia(),
                'type_totals'    => $typeTotals,
            ],
        ]);
    }
}
