<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'user_name' => 'required|string',
            'user_password' => 'required|string',
        ]);

        // Attempt to login with standard Auth::attempt
        // Laravel expects the key 'password' in the credentials array
        $credentials = [
            'user_name' => $request->user_name,
            'password' => $request->user_password,
        ];

        // Also check if user is active (status_batal = 0 or null)
        $user = User::where('user_name', $request->user_name)->first();
        
        if ($user) {
            // Check plain text password directly
            $valid = ($request->user_password === $user->user_password);

            if ($valid) {
                Auth::login($user);
                $request->session()->regenerate();
                return redirect()->intended('dashboard');
            }
        }

        return back()->withErrors([
            'user_name' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ])->onlyInput('user_name');
    }

    public function logout(Request $request)
    {
        \Illuminate\Support\Facades\Cache::forget('sidebar_moduls_user_' . Auth::id());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
