<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnifiedLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Check if user exists and has roles
            if (!$user) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Unable to authenticate user.',
                ])->onlyInput('email');
            }

            // Redirect based on user role
            if ($user->hasRole('admin')) {
                return redirect('/admin');
            } elseif ($user->hasRole('teacher')) {
                return redirect('/teacher');
            } elseif ($user->hasRole('parent')) {
                return redirect('/parent');
            } elseif ($user->hasRole('student')) {
                return redirect('/student');
            }

            // Default fallback
            return redirect('/admin');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            Auth::logout();
            
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect('/login');
    }
}
