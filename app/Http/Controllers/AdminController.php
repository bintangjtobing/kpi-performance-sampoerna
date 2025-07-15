<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DailyProgress;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function getAllUsers(Request $request)
    {
        $currentUser = User::find(session('user_id'));
        
        if (!$currentUser || !$currentUser->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $users = User::with(['dailyProgress' => function($query) {
            $query->latest()->limit(5);
        }])
        ->get()
        ->map(function($user) {
            $lastProgress = $user->dailyProgress->first();
            $thisMonthProgress = DailyProgress::where('user_id', $user->id)
                ->whereMonth('progress_date', Carbon::now()->month)
                ->whereYear('progress_date', Carbon::now()->year)
                ->count();
            
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'whatsapp' => $user->whatsapp,
                'is_admin' => $user->is_admin,
                'last_progress' => $lastProgress ? [
                    'date' => $lastProgress->progress_date->format('Y-m-d'),
                    'percentage' => $lastProgress->overall_percentage
                ] : null,
                'this_month_count' => $thisMonthProgress,
                'created_at' => $user->created_at->format('Y-m-d H:i:s')
            ];
        });

        return response()->json(['users' => $users]);
    }

    public function getUserStats(Request $request)
    {
        $currentUser = User::find(session('user_id'));
        
        if (!$currentUser || !$currentUser->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $totalUsers = User::count();
        $activeUsersThisMonth = User::whereHas('dailyProgress', function($query) {
            $query->whereMonth('progress_date', Carbon::now()->month)
                  ->whereYear('progress_date', Carbon::now()->year);
        })->count();

        $totalProgressToday = DailyProgress::whereDate('progress_date', Carbon::today())->count();
        $avgPerformanceThisMonth = DailyProgress::whereMonth('progress_date', Carbon::now()->month)
            ->whereYear('progress_date', Carbon::now()->year)
            ->avg('overall_percentage');

        $topPerformers = User::with(['dailyProgress' => function($query) {
            $query->whereMonth('progress_date', Carbon::now()->month)
                  ->whereYear('progress_date', Carbon::now()->year);
        }])
        ->get()
        ->map(function($user) {
            $monthlyProgress = $user->dailyProgress;
            $avgPerformance = $monthlyProgress->avg('overall_percentage');
            $progressCount = $monthlyProgress->count();
            
            return [
                'user' => $user,
                'avg_performance' => round($avgPerformance, 2),
                'progress_count' => $progressCount,
                'consistency_score' => $progressCount > 0 ? round(($progressCount / Carbon::now()->day) * 100, 2) : 0
            ];
        })
        ->filter(function($item) {
            return $item['progress_count'] > 0;
        })
        ->sortByDesc('avg_performance')
        ->take(10)
        ->values();

        return response()->json([
            'total_users' => $totalUsers,
            'active_users_this_month' => $activeUsersThisMonth,
            'total_progress_today' => $totalProgressToday,
            'avg_performance_this_month' => round($avgPerformanceThisMonth, 2),
            'top_performers' => $topPerformers
        ]);
    }

    public function updateUserAdmin(Request $request)
    {
        $currentUser = User::find(session('user_id'));
        
        if (!$currentUser || !$currentUser->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'is_admin' => 'required|boolean'
        ]);

        $user = User::find($request->user_id);
        
        if ($user->id === $currentUser->id) {
            return response()->json(['error' => 'Cannot modify your own admin status'], 422);
        }

        $user->update(['is_admin' => $request->is_admin]);

        return response()->json([
            'success' => true,
            'message' => 'User admin status updated successfully',
            'user' => $user
        ]);
    }

    public function deleteUser(Request $request)
    {
        $currentUser = User::find(session('user_id'));
        
        if (!$currentUser || !$currentUser->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::find($request->user_id);
        
        if ($user->id === $currentUser->id) {
            return response()->json(['error' => 'Cannot delete your own account'], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }
}
