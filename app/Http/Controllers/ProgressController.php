<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyProgress;
use App\Models\ProgressItem;
use App\Models\Target;
use App\Models\User;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

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
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: ' . implode(', ', $errors)
            ], 422);
        }

        $user = User::find($request->user_id);
        $today = now()->format('Y-m-d');

        $existingProgress = DailyProgress::where('user_id', $user->id)
            ->where('progress_date', $today)
            ->first();

        if ($existingProgress) {
            return response()->json([
                'success' => false,
                'message' => 'Progress sudah disubmit untuk hari ini'
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
            'progress_date' => $today,
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
}
