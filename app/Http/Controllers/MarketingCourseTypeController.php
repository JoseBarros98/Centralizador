<?php

namespace App\Http\Controllers;

use App\Models\MarketingCourseType;
use App\Models\MarketingCourseTypeHiddenMonth;
use App\Models\MarketingGoalWeek;
use App\Models\MarketingGoalWeekEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MarketingCourseTypeController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $maxOrder = MarketingCourseType::max('sort_order') ?? 0;
        $type = MarketingCourseType::create([
            'name'       => $data['name'],
            'sort_order' => $maxOrder + 1,
            'active'     => true,
        ]);

        // Create entries for all existing weeks so data can be tracked retroactively
        MarketingGoalWeek::all()->each(function ($week) use ($type) {
            MarketingGoalWeekEntry::firstOrCreate(
                ['week_id' => $week->id, 'course_type_id' => $type->id],
                ['completo' => 0, 'adelanto' => 0, 'completando' => 0]
            );
        });

        return back()->with('success', "Tipo de inscripción \"{$type->name}\" agregado.");
    }

    public function toggleMonth(Request $request, MarketingCourseType $marketingCourseType): RedirectResponse
    {
        $data = $request->validate([
            'month' => 'required|integer|between:1,12',
            'year'  => 'required|integer|min:2020',
        ]);

        $existing = MarketingCourseTypeHiddenMonth::where([
            'course_type_id' => $marketingCourseType->id,
            'month'          => $data['month'],
            'year'           => $data['year'],
        ])->first();

        if ($existing) {
            $existing->delete();
            $msg = "Tipo \"{$marketingCourseType->name}\" visible en este mes.";
        } else {
            MarketingCourseTypeHiddenMonth::create([
                'course_type_id' => $marketingCourseType->id,
                'month'          => $data['month'],
                'year'           => $data['year'],
            ]);
            $msg = "Tipo \"{$marketingCourseType->name}\" oculto en este mes (los datos se conservan).";
        }

        return back()->with('success', $msg);
    }
}
