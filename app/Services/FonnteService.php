<?php

namespace App\Services;

use App\Models\User;
use App\Models\DailyProgress;
use Carbon\Carbon;

class FonnteService
{
    private $apiUrl = 'https://api.fonnte.com/send';
    private $token;

    public function __construct()
    {
        $this->token = env('FONNTE_TOKEN', 'KfrsmaB4hjEJspwqe58t');
    }

    public function sendDailyReport(User $user, DailyProgress $dailyProgress, float $overallPercentage)
    {
        $message = $this->formatDailyReportMessage($user, $dailyProgress, $overallPercentage);

        return $this->sendMessage($user->whatsapp, $message);
    }

    public function sendVerificationCode($whatsappNumber, $verificationCode, $userName = null)
    {
        $message = $this->formatVerificationCodeMessage($verificationCode, $userName);

        return $this->sendMessage($whatsappNumber, $message);
    }

    private function formatDailyReportMessage(User $user, DailyProgress $dailyProgress, float $overallPercentage)
    {
        // Format tanggal dalam bahasa Indonesia
        $date = Carbon::parse($dailyProgress->progress_date ?? $dailyProgress->created_at);
        $dayName = $this->getDayNameInIndonesian($date->format('l'));
        $formattedDate = $dayName . ', ' . $date->format('d') . ' ' . $this->getMonthNameInIndonesian($date->format('n')) . ' ' . $date->format('Y');

        // Get progress items data
        $progressItems = $dailyProgress->progressItems->keyBy('item_name');

        $message = "*Daily Report DPC Sibolga TPSP036*\n";
        $message .= "Hari/Tanggal : {$formattedDate}\n\n";

        // Visit Section
        $message .= "*Visit*\n";
        $message .= "Plan Visit : " . ($progressItems->get('Plan Visit')?->target_value ?? 0) . "\n";
        $message .= "Actual Visit : " . ($progressItems->get('Actual Visit')?->actual_value ?? 0) . "\n";
        $message .= "OOR Outlet : " . ($progressItems->get('OOR Outlet')?->actual_value ?? 0) . "\n";
        $message .= "Eff Outlet : " . ($progressItems->get('Eff Outlet')?->actual_value ?? 0) . "\n\n";

        // Ecosystem Section
        $message .= "*Ecosystem*\n";
        $message .= "1. Submit AYO B2B : \n";
        $message .= "     Login AYO : \n";
        $message .= "2. Submit DTE :\n";
        $message .= "     - Chiller Coca-Cola : \n";
        $message .= "     - Garuda food : \n";
        $message .= "     - B2B AIR SRC : \n";
        $message .= "     - Okky Jelly Drink : \n";
        $message .= "     - Misi ABC : \n";
        $message .= "3. Submit CITA : " . ($progressItems->get('Submit CITA')?->actual_value ?? 0) . "\n";
        $message .= "4. Login CITA : " . ($progressItems->get('Login CITA')?->actual_value ?? 0) . "\n\n";

        // Volume Section
        $message .= "*Volume*\n";
        $message .= "Volume DTC12 : " . ($progressItems->get('Volume DTC12')?->actual_value ?? 0) . "\n";
        $message .= "Volume NAT20 : " . ($progressItems->get('Volume NAT20')?->actual_value ?? 0) . "\n";
        $message .= "Volume TWP16 : " . ($progressItems->get('Volume TWP16')?->actual_value ?? 0) . "\n";
        $message .= "Volume VEEV : " . ($progressItems->get('Volume VEEV')?->actual_value ?? 0) . "\n";
        $message .= "Volume KBL12 : " . ($progressItems->get('Volume KBL12')?->actual_value ?? 0) . "\n\n";

        // Eff Call Section
        $message .= "*Eff Call*\n";
        $message .= "Eff DTC12 : " . ($progressItems->get('Eff DTC12')?->actual_value ?? 0) . "\n";
        $message .= "Eff NAT20 : " . ($progressItems->get('Eff NAT20')?->actual_value ?? 0) . "\n";
        $message .= "Eff TWP16 : " . ($progressItems->get('Eff TWP16')?->actual_value ?? 0) . "\n";
        $message .= "Eff Veev   : " . ($progressItems->get('Eff Veev')?->actual_value ?? 0) . "\n";
        $message .= "Eff KBL12 : " . ($progressItems->get('Eff KBL12')?->actual_value ?? 0) . "\n\n";

        // Av Out Section
        $message .= "*Av Out*\n";
        $message .= "Av. Out DTC12 : " . ($progressItems->get('Av. Out DTC12')?->actual_value ?? 0) . "\n";
        $message .= "Av. Out NAT20 : " . ($progressItems->get('Av. Out NAT20')?->actual_value ?? 0) . "\n";
        $message .= "Av. Out TWP16 : " . ($progressItems->get('Av. Out TWP16')?->actual_value ?? 0) . "\n";
        $message .= "Av. Out Veev   : " . ($progressItems->get('Av. Out Veev')?->actual_value ?? 0) . "\n";
        $message .= "Av. Out KBL12 : " . ($progressItems->get('Av. Out KBL12')?->actual_value ?? 0) . "\n\n";

        // Stick Selling Section
        $message .= "*Stick Selling*\n";
        $message .= "Stick Sell DTC12 : " . ($progressItems->get('Stick Sell DTC12')?->actual_value ?? 0) . "\n";
        $message .= "Stick Sell NAT20 : " . ($progressItems->get('Stick Sell NAT20')?->actual_value ?? 0) . "\n";
        $message .= "Stick Sell TWP16 : " . ($progressItems->get('Stick Sell TWP16')?->actual_value ?? 0) . "\n";
        $message .= "Stick Sell KBL12 : " . ($progressItems->get('Stick Sell KBL12')?->actual_value ?? 0) . "\n\n";

        // Private Label & Cricket Section
        $message .= "*Private Label & Cricket*\n";
        $message .= "1. Eff Cricket : " . ($progressItems->get('Eff Cricket')?->actual_value ?? 0) . "\n";
        $message .= "2. Vol Cricket : " . ($progressItems->get('Vol Cricket')?->actual_value ?? 0) . "\n";
        $message .= "3. New Handling Cricket : " . ($progressItems->get('New Handling Cricket')?->actual_value ?? 0) . "\n";
        $message .= "4. Eff ADK EsErCe : \n";
        $message .= "5. Volume ADKEsErCe: \n\n";

        // Additional Items
        $message .= "Bookmarking Cita: " . ($progressItems->get('Bookmarking Cita')?->actual_value ?? 0) . "\n";
        $message .= "PVP NAT20 : " . ($progressItems->get('PVP NAT20')?->actual_value ?? 0) . "\n";
        $message .= "PVP TWP16: " . ($progressItems->get('PVP TWP16')?->actual_value ?? 0) . "\n";
        $message .= "PVP Veev : " . ($progressItems->get('PVP Veev')?->actual_value ?? 0) . "\n";
        $message .= "New Referal SMB: " . ($progressItems->get('New Referal SMB')?->actual_value ?? 0) . "\n";
        $message .= "Comply YAP: " . ($progressItems->get('Comply YAP')?->actual_value ?? '') . "\n\n";

        $message .= "Sekian Laporan dari {$user->name}";
        $message .= "Demikian, terima kasih pak 🙏🏻\n";

        return $message;
    }

    private function formatVerificationCodeMessage($verificationCode, $userName = null)
    {
        $greeting = $userName ? "Halo *{$userName}*! 👋" : "Halo! 👋";

        $message = "*🔐 Verifikasi Email*\n";
        $message .= "Report Daily Helper - Philip Morris International\n\n";
        $message .= "{$greeting}\n\n";
        $message .= "Terima kasih telah mendaftar di sistem Report Daily Helper. Untuk melanjutkan proses registrasi, silakan verifikasi email Anda dengan kode berikut:\n\n";
        $message .= "🎯 *Kode Verifikasi Anda:*\n";
        $message .= "```{$verificationCode}```\n\n";
        $message .= "📋 *Cara Menggunakan:*\n";
        $message .= "1. Kembali ke halaman registrasi\n";
        $message .= "2. Masukkan kode *{$verificationCode}* pada kolom verifikasi\n";
        $message .= "3. Klik tombol \"Verifikasi Email\"\n\n";
        $message .= "⏰ *Penting:* Kode ini akan kadaluarsa dalam 15 menit\n\n";
        $message .= "🔒 *Keamanan:* Jangan bagikan kode ini kepada siapa pun\n\n";
        $message .= "Jika Anda tidak melakukan registrasi ini, abaikan pesan ini.\n\n";
        $message .= "Butuh bantuan? Hubungi: hello@bintangtobing.com";

        return $message;
    }

    private function sendMessage($target, $message)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'target' => $target,
                'message' => $message,
                'countryCode' => '62',
                'typing' => false,
            ),
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $this->token
            ),
        ));

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            curl_close($curl);
            throw new \Exception("CURL Error: " . $error_msg);
        }

        curl_close($curl);

        $decodedResponse = json_decode($response, true);

        if ($httpCode !== 200) {
            throw new \Exception("HTTP Error {$httpCode}: " . $response);
        }

        return $decodedResponse;
    }

    private function getDayNameInIndonesian($englishDay)
    {
        $days = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu'
        ];

        return $days[$englishDay] ?? $englishDay;
    }

    private function getMonthNameInIndonesian($monthNumber)
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        return $months[$monthNumber] ?? $monthNumber;
    }
}
