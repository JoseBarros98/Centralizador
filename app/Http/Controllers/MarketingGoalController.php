<?php

namespace App\Http\Controllers;

use App\Models\MarketingCourseType;
use App\Models\MarketingCourseTypeHiddenMonth;
use App\Models\MarketingGoal;
use App\Models\MarketingGoalWeek;
use App\Models\MarketingGoalWeekEntry;
use App\Models\MarketingTeam;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class MarketingGoalController extends Controller
{
    public function index(Request $request): View
    {
        $month  = (int) $request->get('month', date('n'));
        $year   = (int) $request->get('year', date('Y'));
        $teamId = $request->get('team_id');

        $goals = MarketingGoal::with(['user', 'team', 'weeks.entries'])
            ->where('month', $month)
            ->where('year', $year)
            ->when($teamId, fn($q) => $q->where('team_id', $teamId))
            ->get()
            ->sortBy('user.name')
            ->values();

        // Recalculate debt dynamically from previous month data
        $prevMonth = $month == 1 ? 12 : $month - 1;
        $prevYear  = $month == 1 ? $year - 1 : $year;

        if ($goals->isNotEmpty()) {
            $prevGoals = MarketingGoal::with('weeks.entries')
                ->whereIn('user_id', $goals->pluck('user_id'))
                ->where('month', $prevMonth)
                ->where('year', $prevYear)
                ->get()
                ->keyBy('user_id');

            foreach ($goals as $goal) {
                $prev = $prevGoals->get($goal->user_id);
                $goal->debt_from_previous = $prev
                    ? max(0, $prev->monthly_goal + $prev->debt_from_previous - $prev->nationalTotal())
                    : 0;
            }
        }

        $users       = User::role('marketing')->orderBy('name')->get();
        $teams       = MarketingTeam::where('active', true)->orderBy('name')->get();
        $courseTypes = MarketingCourseType::where('active', true)->orderBy('sort_order')->get();

        $hiddenTypeIds = MarketingCourseTypeHiddenMonth::where('month', $month)
            ->where('year', $year)
            ->pluck('course_type_id')
            ->toArray();

        return view('marketing-goals.index', compact(
            'goals', 'users', 'teams', 'courseTypes', 'hiddenTypeIds',
            'month', 'year', 'teamId'
        ));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'user_id'      => 'required|exists:users,id',
            'team_id'      => 'nullable|exists:marketing_teams,id',
            'month'        => 'required|integer|between:1,12',
            'year'         => 'required|integer|min:2020',
            'monthly_goal' => 'required|integer|min:0',
        ]);

        $prevMonth = $data['month'] == 1 ? 12 : $data['month'] - 1;
        $prevYear  = $data['month'] == 1 ? $data['year'] - 1 : $data['year'];

        $prevGoal = MarketingGoal::with('weeks.entries')
            ->where('user_id', $data['user_id'])
            ->where('month', $prevMonth)
            ->where('year', $prevYear)
            ->first();

        $debt = 0;
        if ($prevGoal) {
            $debt = max(0, $prevGoal->monthly_goal + $prevGoal->debt_from_previous - $prevGoal->nationalTotal());
        }

        $goal = MarketingGoal::create([
            'user_id'            => $data['user_id'],
            'team_id'            => $data['team_id'],
            'month'              => $data['month'],
            'year'               => $data['year'],
            'monthly_goal'       => $data['monthly_goal'],
            'debt_from_previous' => $debt,
        ]);

        $courseTypes = MarketingCourseType::where('active', true)->orderBy('sort_order')->get();

        foreach (range(1, 4) as $weekNum) {
            $week = MarketingGoalWeek::create([
                'goal_id'     => $goal->id,
                'week_number' => $weekNum,
            ]);
            foreach ($courseTypes as $ct) {
                MarketingGoalWeekEntry::create([
                    'week_id'        => $week->id,
                    'course_type_id' => $ct->id,
                ]);
            }
        }

        return redirect()->route('marketing-goals.index', [
            'month'   => $data['month'],
            'year'    => $data['year'],
            'team_id' => $data['team_id'],
        ])->with('success', 'Meta creada correctamente.');
    }

    public function update(Request $request, MarketingGoal $marketingGoal): JsonResponse
    {
        $data = $request->validate([
            'monthly_goal' => 'required|integer|min:0',
        ]);

        $marketingGoal->update($data);
        $marketingGoal->load('weeks.entries');

        return response()->json([
            'monthly_goal' => $marketingGoal->monthly_goal,
            'meta_total'   => $marketingGoal->metaTotal(),
            'diferencia'   => $marketingGoal->diferencia(),
        ]);
    }

    public function destroy(MarketingGoal $marketingGoal): \Illuminate\Http\RedirectResponse
    {
        $month  = $marketingGoal->month;
        $year   = $marketingGoal->year;
        $teamId = $marketingGoal->team_id;

        $marketingGoal->delete();

        return redirect()->route('marketing-goals.index', [
            'month'   => $month,
            'year'    => $year,
            'team_id' => $teamId,
        ])->with('success', 'Meta eliminada correctamente.');
    }
}
