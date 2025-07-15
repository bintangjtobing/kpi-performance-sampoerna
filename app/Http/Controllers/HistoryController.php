<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyProgress;
use App\Models\User;
use Carbon\Carbon;

class HistoryController extends Controller
{
    public function getMonthlyData(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12'
        ]);

        $user = User::find($request->user_id);
        $currentUser = User::find(session('user_id'));
        
        if (!$currentUser || (!$currentUser->is_admin && $currentUser->id !== $user->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $startDate = Carbon::create($request->year, $request->month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $progressData = DailyProgress::where('user_id', $request->user_id)
            ->whereBetween('progress_date', [$startDate, $endDate])
            ->with('progressItems')
            ->get()
            ->keyBy(function($item) {
                return $item->progress_date->format('Y-m-d');
            });

        $calendar = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $dateString = $currentDate->format('Y-m-d');
            $calendar[$dateString] = [
                'date' => $currentDate->format('d'),
                'has_progress' => isset($progressData[$dateString]),
                'percentage' => isset($progressData[$dateString]) ? $progressData[$dateString]->overall_percentage : null,
                'day_name' => $currentDate->format('l')
            ];
            $currentDate->addDay();
        }

        return response()->json([
            'calendar' => $calendar,
            'month_name' => $startDate->format('F Y'),
            'total_days' => $endDate->day,
            'progress_count' => count($progressData)
        ]);
    }

    public function getProgressDetail(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date'
        ]);

        $user = User::find($request->user_id);
        $currentUser = User::find(session('user_id'));
        
        if (!$currentUser || (!$currentUser->is_admin && $currentUser->id !== $user->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $progress = DailyProgress::where('user_id', $request->user_id)
            ->where('progress_date', $request->date)
            ->with('progressItems')
            ->first();

        if (!$progress) {
            return response()->json(['error' => 'No progress found for this date'], 404);
        }

        $groupedItems = $progress->progressItems->groupBy(function($item) {
            if (str_contains($item->item_name, 'Visit')) return 'Visit';
            if (str_contains($item->item_name, 'Submit') || str_contains($item->item_name, 'Login')) return 'Ecosystem';
            if (str_contains($item->item_name, 'Volume')) return 'Volume';
            if (str_contains($item->item_name, 'Eff') && !str_contains($item->item_name, 'Cricket')) return 'Eff Call';
            if (str_contains($item->item_name, 'Av. Out')) return 'Av Out';
            if (str_contains($item->item_name, 'Stick Sell')) return 'Stick Selling';
            if (str_contains($item->item_name, 'Cricket') || str_contains($item->item_name, 'ADK')) return 'Private Label & Cricket';
            return 'Others';
        });

        \Log::info('Progress detail response:', [
            'progress_id' => $progress->id,
            'photos' => $progress->photos,
            'photos_count' => count($progress->photos ?? [])
        ]);

        return response()->json([
            'progress' => $progress,
            'grouped_items' => $groupedItems,
            'user' => $user
        ]);
    }

    public function getAvailableMonths(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::find($request->user_id);
        $currentUser = User::find(session('user_id'));
        
        if (!$currentUser || (!$currentUser->is_admin && $currentUser->id !== $user->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $months = DailyProgress::where('user_id', $request->user_id)
            ->selectRaw('YEAR(progress_date) as year, MONTH(progress_date) as month')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'year' => $item->year,
                    'month' => $item->month,
                    'display' => Carbon::create($item->year, $item->month, 1)->format('F Y')
                ];
            });

        return response()->json(['months' => $months]);
    }
}
