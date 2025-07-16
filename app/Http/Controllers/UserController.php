<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EmailVerification;
use App\Mail\EmailVerificationCode;
use Illuminate\Support\Facades\Mail;
use App\Services\FonnteService;

class UserController extends Controller
{
    public function checkUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = User::where('name', $request->name)->first();

        if ($user) {
            return response()->json([
                'exists' => true,
                'user' => $user,
                'step' => 'password'
            ]);
        }

        return response()->json([
            'exists' => false,
            'step' => 'register'
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'whatsapp' => 'required|string|regex:/^628[0-9]{8,11}$/',
            'password' => 'required|string|min:6',
        ]);

        // Store registration data temporarily in session
        session([
            'registration_data' => [
                'name' => $request->name,
                'email' => $request->email,
                'whatsapp' => $request->whatsapp,
                'password' => bcrypt($request->password),
            ]
        ]);

        // Generate and send verification code
        $verification = EmailVerification::generateCode($request->email);
        
        $emailSent = false;
        $whatsappSent = false;
        
        // Send email verification
        try {
            Mail::to($request->email)->send(new EmailVerificationCode(
                $verification->code,
                $request->name,
                15 // 15 minutes expiry
            ));
            $emailSent = true;
        } catch (\Exception $e) {
            error_log("Email verification failed: " . $e->getMessage());
        }
        
        // Send WhatsApp verification
        try {
            $fonnteService = new FonnteService();
            $fonnteService->sendVerificationCode(
                $request->whatsapp,
                $verification->code,
                $request->name
            );
            $whatsappSent = true;
        } catch (\Exception $e) {
            error_log("WhatsApp verification failed: " . $e->getMessage());
        }
        
        if (!$emailSent && !$whatsappSent) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim kode verifikasi. Silakan coba lagi.'
            ], 500);
        }
        
        $message = 'Kode verifikasi telah dikirim ke ';
        if ($emailSent && $whatsappSent) {
            $message .= 'email dan WhatsApp Anda.';
        } elseif ($emailSent) {
            $message .= 'email Anda.';
        } else {
            $message .= 'WhatsApp Anda.';
        }

        return response()->json([
            'success' => true,
            'step' => 'email_verification',
            'message' => $message,
            'email' => $request->email,
            'email_sent' => $emailSent,
            'whatsapp_sent' => $whatsappSent
        ]);
    }

    public function sendVerificationCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $registrationData = session('registration_data');
        if (!$registrationData || $registrationData['email'] !== $request->email) {
            return response()->json([
                'success' => false,
                'message' => 'Data registrasi tidak ditemukan. Silakan mulai registrasi dari awal.'
            ], 400);
        }

        // Generate new verification code
        $verification = EmailVerification::generateCode($request->email);
        
        $emailSent = false;
        $whatsappSent = false;
        
        // Send email verification
        try {
            Mail::to($request->email)->send(new EmailVerificationCode(
                $verification->code,
                $registrationData['name'],
                15
            ));
            $emailSent = true;
        } catch (\Exception $e) {
            error_log("Email verification failed: " . $e->getMessage());
        }
        
        // Send WhatsApp verification
        try {
            $fonnteService = new FonnteService();
            $fonnteService->sendVerificationCode(
                $registrationData['whatsapp'],
                $verification->code,
                $registrationData['name']
            );
            $whatsappSent = true;
        } catch (\Exception $e) {
            error_log("WhatsApp verification failed: " . $e->getMessage());
        }
        
        if (!$emailSent && !$whatsappSent) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim kode verifikasi. Silakan coba lagi.'
            ], 500);
        }
        
        $message = 'Kode verifikasi telah dikirim ke ';
        if ($emailSent && $whatsappSent) {
            $message .= 'email dan WhatsApp Anda.';
        } elseif ($emailSent) {
            $message .= 'email Anda.';
        } else {
            $message .= 'WhatsApp Anda.';
        }
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'email_sent' => $emailSent,
            'whatsapp_sent' => $whatsappSent
        ]);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $registrationData = session('registration_data');
        if (!$registrationData || $registrationData['email'] !== $request->email) {
            return response()->json([
                'success' => false,
                'message' => 'Data registrasi tidak ditemukan. Silakan mulai registrasi dari awal.'
            ], 400);
        }

        // Verify the code
        if (!EmailVerification::verifyCode($request->email, $request->code)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode verifikasi tidak valid atau sudah kadaluarsa. Silakan minta kode baru.'
            ], 400);
        }

        // Create user after successful verification
        $username = str_replace(['@', '.'], ['_', '_'], explode('@', $request->email)[0]);
        $counter = 1;
        $originalUsername = $username;
        
        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . '_' . $counter;
            $counter++;
        }

        $user = User::create([
            'name' => $registrationData['name'],
            'email' => $registrationData['email'],
            'username' => $username,
            'whatsapp' => $registrationData['whatsapp'],
            'password' => $registrationData['password'],
            'email_verified_at' => now(),
        ]);

        // Clear registration data from session
        session()->forget('registration_data');

        return response()->json([
            'success' => true,
            'user' => $user,
            'message' => 'Email berhasil diverifikasi. Akun Anda telah dibuat!'
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('name', $request->name)->first();

        if (!$user || !password_verify($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password salah. Silakan coba lagi.'
            ], 401);
        }

        session(['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'user' => $user,
            'message' => 'Login successful'
        ]);
    }
}
