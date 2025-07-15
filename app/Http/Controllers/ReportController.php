<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyProgress;
use App\Models\User;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ReportController extends Controller
{
    public function getAvailableReports(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::find($request->user_id);
        $currentUser = User::find(session('user_id'));
        
        if (!$currentUser || (!$currentUser->is_admin && $currentUser->id !== $user->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        $availableReports = DailyProgress::where('user_id', $request->user_id)
            ->selectRaw('YEAR(progress_date) as year, MONTH(progress_date) as month, COUNT(*) as progress_count')
            ->groupBy('year', 'month')
            ->having('progress_count', '>=', 20) // Minimum 20 days of progress
            ->where(function($query) use ($currentYear, $currentMonth) {
                $query->where('year', '<', $currentYear)
                    ->orWhere(function($q) use ($currentYear, $currentMonth) {
                        $q->where('year', '=', $currentYear)
                          ->where('month', '<', $currentMonth);
                    });
            })
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'year' => $item->year,
                    'month' => $item->month,
                    'progress_count' => $item->progress_count,
                    'display' => Carbon::create($item->year, $item->month, 1)->format('F Y'),
                    'is_complete' => $item->progress_count >= 20
                ];
            });

        return response()->json(['reports' => $availableReports]);
    }

    public function getMonthlyReport(Request $request)
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
            ->orderBy('progress_date')
            ->get();

        if ($progressData->isEmpty()) {
            return response()->json(['error' => 'No data found for this month'], 404);
        }

        $summary = [
            'total_days' => $endDate->day,
            'progress_days' => $progressData->count(),
            'completion_rate' => round(($progressData->count() / $endDate->day) * 100, 2),
            'average_performance' => round($progressData->avg('overall_percentage'), 2),
            'best_performance' => round($progressData->max('overall_percentage'), 2),
            'worst_performance' => round($progressData->min('overall_percentage'), 2),
        ];

        $categorySummary = [];
        foreach ($progressData as $progress) {
            foreach ($progress->progressItems as $item) {
                $category = $this->getCategoryName($item->item_name);
                if (!isset($categorySummary[$category])) {
                    $categorySummary[$category] = [
                        'total_target' => 0,
                        'total_actual' => 0,
                        'total_percentage' => 0,
                        'count' => 0
                    ];
                }
                $categorySummary[$category]['total_target'] += $item->target_value;
                $categorySummary[$category]['total_actual'] += $item->actual_value;
                $categorySummary[$category]['total_percentage'] += $item->percentage;
                $categorySummary[$category]['count']++;
            }
        }

        foreach ($categorySummary as $category => $data) {
            $categorySummary[$category]['average_percentage'] = round($data['total_percentage'] / $data['count'], 2);
            $categorySummary[$category]['achievement_rate'] = round(($data['total_actual'] / $data['total_target']) * 100, 2);
        }

        return response()->json([
            'user' => $user,
            'period' => $startDate->format('F Y'),
            'summary' => $summary,
            'category_summary' => $categorySummary,
            'daily_progress' => $progressData,
            'can_download' => $progressData->count() >= 20
        ]);
    }

    public function downloadMonthlyReport(Request $request)
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

        $reportData = $this->getMonthlyReport($request);
        $data = $reportData->getData(true);

        if (isset($data['error'])) {
            return response()->json(['error' => $data['error']], 404);
        }

        try {
            $pdf = Pdf::loadView('reports.monthly', $data);
            $filename = 'Monthly_Report_' . $data['user']['name'] . '_' . $data['period'] . '.pdf';
            
            // Get PDF content 
            $pdfContent = $pdf->output();
            
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function downloadDailyReport(Request $request)
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
            return $this->getCategoryName($item->item_name);
        });

        // Download photos temporarily
        $tempPhotos = [];
        $localPhotos = [];
        if ($progress->photos && count($progress->photos) > 0) {
            $tempPhotos = $this->downloadPhotosTemporarily($progress->photos);
            $localPhotos = $tempPhotos;
        }

        $data = [
            'user' => $user,
            'progress' => $progress,
            'grouped_items' => $groupedItems,
            'date' => Carbon::parse($request->date)->format('d F Y'),
            'photos' => $localPhotos
        ];

        try {
            $pdf = Pdf::loadView('reports.daily', $data);
            $filename = 'Daily_Report_' . $user->name . '_' . $data['date'] . '.pdf';
            
            // Get PDF content before cleanup
            $pdfContent = $pdf->output();
            
            // Cleanup temporary photos
            if (!empty($tempPhotos)) {
                $this->cleanupTempPhotos($tempPhotos);
            }
            
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);
        } catch (\Exception $e) {
            // Cleanup on error
            if (!empty($tempPhotos)) {
                $this->cleanupTempPhotos($tempPhotos);
            }
            throw $e;
        }
    }

    public function previewDailyReport(Request $request)
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
            return $this->getCategoryName($item->item_name);
        });

        // Download photos temporarily
        $tempPhotos = [];
        $localPhotos = [];
        if ($progress->photos && count($progress->photos) > 0) {
            $tempPhotos = $this->downloadPhotosTemporarily($progress->photos);
            $localPhotos = $tempPhotos;
        }

        $data = [
            'user' => $user,
            'progress' => $progress,
            'grouped_items' => $groupedItems,
            'date' => Carbon::parse($request->date)->format('d F Y'),
            'photos' => $localPhotos
        ];

        try {
            $pdf = Pdf::loadView('reports.daily', $data);
            
            // Get PDF content before cleanup
            $pdfContent = $pdf->output();
            
            // Cleanup temporary photos
            if (!empty($tempPhotos)) {
                $this->cleanupTempPhotos($tempPhotos);
            }
            
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="Daily_Report_Preview.pdf"'
            ]);
        } catch (\Exception $e) {
            // Cleanup on error
            if (!empty($tempPhotos)) {
                $this->cleanupTempPhotos($tempPhotos);
            }
            throw $e;
        }
    }

    public function previewMonthlyReport(Request $request)
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

        $reportData = $this->getMonthlyReport($request);
        $data = $reportData->getData(true);

        if (isset($data['error'])) {
            return response()->json(['error' => $data['error']], 404);
        }

        try {
            $pdf = Pdf::loadView('reports.monthly', $data);
            
            // Get PDF content 
            $pdfContent = $pdf->output();
            
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="Monthly_Report_Preview.pdf"'
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function getCategoryName($itemName)
    {
        if (str_contains($itemName, 'Visit')) return 'Visit';
        if (str_contains($itemName, 'Submit') || str_contains($itemName, 'Login')) return 'Ecosystem';
        if (str_contains($itemName, 'Volume')) return 'Volume';
        if (str_contains($itemName, 'Eff') && !str_contains($itemName, 'Cricket')) return 'Eff Call';
        if (str_contains($itemName, 'Av. Out')) return 'Av Out';
        if (str_contains($itemName, 'Stick Sell')) return 'Stick Selling';
        if (str_contains($itemName, 'Cricket') || str_contains($itemName, 'ADK')) return 'Private Label & Cricket';
        return 'Others';
    }

    private function downloadPhotosTemporarily($photoUrls)
    {
        $tempPhotos = [];
        $tempDir = storage_path('app/temp/reports');
        
        // Create temp directory if it doesn't exist
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }
        
        foreach ($photoUrls as $index => $url) {
            try {
                // Get file contents from URL
                $photoContent = file_get_contents($url);
                
                if ($photoContent !== false) {
                    // Extract file extension from URL or default to jpg
                    $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
                    if (empty($extension)) {
                        $extension = 'jpg';
                    }
                    
                    // Generate unique filename
                    $filename = 'photo_' . time() . '_' . $index . '.' . $extension;
                    $tempPath = $tempDir . '/' . $filename;
                    
                    // Save to temp directory
                    file_put_contents($tempPath, $photoContent);
                    
                    // Store temp path for cleanup later
                    $tempPhotos[] = $tempPath;
                    
                    \Log::info("Downloaded photo temporarily", [
                        'url' => $url,
                        'temp_path' => $tempPath,
                        'size' => strlen($photoContent)
                    ]);
                } else {
                    \Log::error("Failed to download photo", ['url' => $url]);
                }
            } catch (\Exception $e) {
                \Log::error("Error downloading photo", [
                    'url' => $url,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $tempPhotos;
    }

    private function cleanupTempPhotos($tempPhotos)
    {
        foreach ($tempPhotos as $tempPath) {
            try {
                if (File::exists($tempPath)) {
                    File::delete($tempPath);
                    \Log::info("Cleaned up temp photo", ['path' => $tempPath]);
                }
            } catch (\Exception $e) {
                \Log::error("Error cleaning up temp photo", [
                    'path' => $tempPath,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Try to remove temp directory if empty
        $tempDir = storage_path('app/temp/reports');
        try {
            if (File::exists($tempDir) && count(File::files($tempDir)) === 0) {
                File::deleteDirectory($tempDir);
            }
        } catch (\Exception $e) {
            \Log::error("Error cleaning up temp directory", [
                'dir' => $tempDir,
                'error' => $e->getMessage()
            ]);
        }
    }
}
