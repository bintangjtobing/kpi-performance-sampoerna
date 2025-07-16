<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyProgress;
use App\Models\ProgressItem;
use App\Models\Target;
use App\Models\User;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Mail\DailyProgressSubmitted;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Services\FonnteService;

class ProgressController extends Controller
{
    public function getProgressItems()
    {
        $items = [
            'Visit' => [
                'Plan Visit' => true,
                'Actual Visit' => true,
                'OOR Outlet' => true,
                'Eff Outlet' => true,
            ],
            'Ecosystem' => [
                'Submit AYO B2B' => false,
                'Login AYO' => false,
                'Submit DTE - Chiller Coca-Cola' => false,
                'Submit DTE - Garuda food' => false,
                'Submit DTE - B2B AIR SRC' => false,
                'Submit DTE - Okky Jelly Drink' => false,
                'Submit DTE - Misi ABC' => false,
                'Submit CITA' => true,
                'Login CITA' => true,
            ],
            'Volume' => [
                'Volume DTC12' => true,
                'Volume NAT20' => true,
                'Volume TWP16' => true,
                'Volume VEEV' => true,
                'Volume KBL12' => true,
            ],
            'Eff Call' => [
                'Eff DTC12' => true,
                'Eff NAT20' => true,
                'Eff TWP16' => true,
                'Eff Veev' => true,
                'Eff KBL12' => true,
            ],
            'Av Out' => [
                'Av. Out DTC12' => true,
                'Av. Out NAT20' => true,
                'Av. Out TWP16' => true,
                'Av. Out Veev' => true,
                'Av. Out KBL12' => true,
            ],
            'Stick Selling' => [
                'Stick Sell DTC12' => true,
                'Stick Sell NAT20' => true,
                'Stick Sell TWP16' => true,
                'Stick Sell KBL12' => true,
            ],
            'Private Label & Cricket' => [
                'Eff Cricket' => true,
                'Vol Cricket' => true,
                'New Handling Cricket' => true,
                'Eff ADK EsErCe' => false,
                'Volume ADKEsErCe' => false,
            ],
            'Others' => [
                'Bookmarking Cita' => true,
                'PVP NAT20' => true,
                'PVP TWP16' => true,
                'PVP Veev' => true,
                'New Referal SMB' => true,
                'Comply YAP' => false,
            ],
        ];

        $target = Target::where('name', 'default')->first();
        $targetValue = $target ? $target->target_value : 42;

        return response()->json([
            'items' => $items,
            'target_value' => $targetValue
        ]);
    }

    public function submitProgress(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'items' => 'required|string',
                'photo_urls' => 'nullable|array',
                'photo_urls.*' => 'url',
                'progress_date' => 'nullable|date|before_or_equal:today', // Allow custom date but not future dates
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $errors)
            ], 422);
        }

        $user = User::find($request->user_id);
        
        // Use custom date if provided, otherwise use today
        $progressDate = $request->progress_date ?? now()->format('Y-m-d');
        
        // Additional validation: ensure the date is not in the future
        if (strtotime($progressDate) > strtotime(now()->format('Y-m-d'))) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat mengirim laporan untuk tanggal yang akan datang'
            ], 422);
        }

        $existingProgress = DailyProgress::where('user_id', $user->id)
            ->where('progress_date', $progressDate)
            ->first();

        if ($existingProgress) {
            $dateFormatted = \Carbon\Carbon::parse($progressDate)->format('d F Y');
            return response()->json([
                'success' => false,
                'message' => "Progress sudah disubmit untuk tanggal {$dateFormatted}"
            ], 422);
        }

        // Use photo URLs directly from Cloudinary
        $photos = $request->photo_urls ?? [];
        \Log::info('Photo URLs received:', ['photos' => $photos]);

        $items = json_decode($request->items, true);
        if (!$items) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid items format'
            ], 422);
        }

        $target = Target::where('name', 'default')->first();
        $targetValue = $target ? $target->target_value : 42;

        $totalPercentage = 0;
        $validItemCount = 0;

        $dailyProgress = DailyProgress::create([
            'user_id' => $user->id,
            'progress_date' => $progressDate,
            'overall_percentage' => 0,
            'photos' => $photos,
        ]);
        
        \Log::info('Daily progress created:', [
            'id' => $dailyProgress->id,
            'photos_saved' => $dailyProgress->photos,
            'photos_count' => count($dailyProgress->photos ?? [])
        ]);

        foreach ($items as $itemName => $actualValue) {
            if ($actualValue !== null && $actualValue !== '') {
                $percentage = ($actualValue / $targetValue) * 100;
                $totalPercentage += $percentage;
                $validItemCount++;

                ProgressItem::create([
                    'daily_progress_id' => $dailyProgress->id,
                    'item_name' => $itemName,
                    'target_value' => $targetValue,
                    'actual_value' => $actualValue,
                    'percentage' => $percentage,
                ]);
            }
        }

        $overallPercentage = $validItemCount > 0 ? $totalPercentage / $validItemCount : 0;

        $dailyProgress->update([
            'overall_percentage' => $overallPercentage
        ]);

        $message = $this->generateFeedbackMessage($user->name, $overallPercentage);

        // Generate PDF and send email
        try {
            $pdfPath = $this->generateDailyReportPDF($user, $dailyProgress);
            
            // Send email with PDF attachment
            Mail::to($user->email)->send(new DailyProgressSubmitted($user, $dailyProgress, $overallPercentage, $message, $pdfPath));
            
            // Clean up temporary PDF file
            if ($pdfPath && file_exists($pdfPath)) {
                unlink($pdfPath);
            }
        } catch (\Exception $e) {
            // Log error but don't fail the request
            error_log("Email sending failed: " . $e->getMessage());
        }

        // Send WhatsApp notification
        try {
            $fonnteService = new FonnteService();
            $fonnteService->sendDailyReport($user, $dailyProgress, $overallPercentage);
        } catch (\Exception $e) {
            // Log error but don't fail the request
            error_log("WhatsApp sending failed: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'overall_percentage' => $overallPercentage,
            'message' => $message,
            'daily_progress' => $dailyProgress
        ]);
    }

    private function generateFeedbackMessage($name, $percentage)
    {
        $targetPercentage = 70;

        if ($percentage >= $targetPercentage) {
            $messages = [
                "Hai {$name}, performance kamu hari ini {$percentage}%! Fantastic job, kamu sudah melampaui target {$targetPercentage}%! 🎉",
                "Wow {$name}! Performance {$percentage}% dari target {$targetPercentage}% nih. Kerja yang luar biasa! 💪",
                "Keren banget {$name}! Dengan performance {$percentage}%, kamu sudah crushing the target! 🔥"
            ];
        } elseif ($percentage >= 50) {
            $messages = [
                "Hai {$name}, performance kamu hari ini {$percentage}% dari target {$targetPercentage}%. Lumayan bagus, tapi masih bisa lebih baik lagi! 💪",
                "Good effort {$name}! Performance {$percentage}% sudah cukup baik, tapi ayo push lagi untuk mencapai {$targetPercentage}%! 🚀",
                "Nice work {$name}! {$percentage}% sudah on track, tinggal sedikit lagi untuk mencapai target {$targetPercentage}%! 📈"
            ];
        } else {
            $messages = [
                "Hai {$name}, performance kamu hari ini {$percentage}% dari target {$targetPercentage}%. Ayo semangat, kamu pasti bisa lebih baik! 💪",
                "Hey {$name}! Performance {$percentage}% masih di bawah target {$targetPercentage}%. Time to step up your game! 🔥",
                "Semangat {$name}! {$percentage}% baru awal, masih banyak waktu untuk improve dan mencapai {$targetPercentage}%! 🚀"
            ];
        }

        return $messages[array_rand($messages)];
    }

    private function generateDailyReportPDF($user, $dailyProgress)
    {
        // Group progress items by category
        $groupedItems = $dailyProgress->progressItems->groupBy(function($item) {
            return $this->getCategoryName($item->item_name);
        });

        // Download photos temporarily
        $tempPhotos = [];
        $localPhotos = [];
        if ($dailyProgress->photos && count($dailyProgress->photos) > 0) {
            $tempPhotos = $this->downloadPhotosTemporarily($dailyProgress->photos);
            $localPhotos = $tempPhotos;
        }

        $data = [
            'user' => $user,
            'progress' => $dailyProgress,
            'grouped_items' => $groupedItems,
            'date' => $dailyProgress->progress_date ? \Carbon\Carbon::parse($dailyProgress->progress_date)->format('d F Y') : now()->format('d F Y'),
            'photos' => $localPhotos
        ];

        try {
            $pdf = Pdf::loadView('reports.daily', $data);
            
            // Save PDF to temporary file
            $filename = 'Daily_Report_' . $user->name . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';
            $pdfPath = storage_path('app/temp/' . $filename);
            
            // Ensure temp directory exists
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }
            
            // Save PDF to file
            file_put_contents($pdfPath, $pdf->output());
            
            // Cleanup temporary photos
            if (!empty($tempPhotos)) {
                $this->cleanupTempPhotos($tempPhotos);
            }
            
            return $pdfPath;
        } catch (\Exception $e) {
            // Cleanup on error
            if (!empty($tempPhotos)) {
                $this->cleanupTempPhotos($tempPhotos);
            }
            throw $e;
        }
    }

    private function getCategoryName($itemName)
    {
        // Define category mappings
        $categoryMapping = [
            'Plan Visit' => 'Visit',
            'Actual Visit' => 'Visit',
            'OOR Outlet' => 'Visit',
            'Plan SO' => 'Sales Order',
            'Actual SO' => 'Sales Order',
            'SSKU' => 'Sales Order',
            'Plan Display' => 'Display',
            'Actual Display' => 'Display',
            'OOS' => 'Display',
            'Plan Sampling' => 'Sampling',
            'Actual Sampling' => 'Sampling',
            'Plan Collection' => 'Collection',
            'Actual Collection' => 'Collection',
            'Plan Kompetitor' => 'Competitor',
            'Actual Kompetitor' => 'Competitor',
            'Plan Ekspansi' => 'Expansion',
            'Actual Ekspansi' => 'Expansion',
            'Plan Reactivation' => 'Reactivation',
            'Actual Reactivation' => 'Reactivation',
            'Plan Coaching' => 'Coaching',
            'Actual Coaching' => 'Coaching',
        ];

        return $categoryMapping[$itemName] ?? 'Other';
    }

    private function downloadPhotosTemporarily($photos)
    {
        $tempPhotos = [];
        foreach ($photos as $photo) {
            try {
                $tempPath = storage_path('app/temp/' . basename($photo));
                $content = file_get_contents($photo);
                file_put_contents($tempPath, $content);
                $tempPhotos[] = $tempPath;
            } catch (\Exception $e) {
                // Skip failed photos
                continue;
            }
        }
        return $tempPhotos;
    }

    private function cleanupTempPhotos($tempPhotos)
    {
        foreach ($tempPhotos as $tempPhoto) {
            if (file_exists($tempPhoto)) {
                unlink($tempPhoto);
            }
        }
    }

    public function getTodayProgress(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $today = now()->format('Y-m-d');
        $progress = DailyProgress::where('user_id', $request->user_id)
            ->where('progress_date', $today)
            ->with('progressItems')
            ->first();

        return response()->json([
            'has_progress' => !!$progress,
            'progress' => $progress
        ]);
    }

    public function getProgressForDate(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date'
        ]);

        $user = User::find($request->user_id);
        $date = $request->date;

        $progress = DailyProgress::where('user_id', $user->id)
            ->where('progress_date', $date)
            ->with('progressItems')
            ->first();

        return response()->json([
            'has_progress' => !!$progress,
            'progress' => $progress,
            'date' => $date,
            'can_submit' => strtotime($date) <= strtotime(now()->format('Y-m-d'))
        ]);
    }
}
