<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

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

        $username = str_replace(['@', '.'], ['_', '_'], explode('@', $request->email)[0]);
        $counter = 1;
        $originalUsername = $username;
        
        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . '_' . $counter;
            $counter++;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $username,
            'whatsapp' => $request->whatsapp,
            'password' => bcrypt($request->password),
        ]);

        return response()->json([
            'success' => true,
            'user' => $user,
            'message' => 'User registered successfully'
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
