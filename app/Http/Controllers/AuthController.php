<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AuthController extends Controller
{
    // ================= REGISTER FORM =================
    public function showRegisterForm() {
        return view('auth.register');
    }

    // ================= REGISTER =================
    public function register(Request $request) {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        $otp = rand(100000, 999999);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'otp' => (string)$otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10),
            'is_verified' => false,
        ]);

        Mail::raw("Your OTP is: $otp", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('OTP Verification');
        });

        session(['otp_email' => $user->email]);

        return redirect()->route('otp.form');
    }

    // ================= OTP FORM =================
    public function showOtpForm() {
        return view('auth.otp');
    }

    // ================= VERIFY OTP =================
    public function verifyOtp(Request $request) {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $email = session('otp_email');
        if (!$email) {
            return redirect()->route('register.form');
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return back()->withErrors(['otp' => 'User not found']);
        }

        if ((string)$request->otp !== (string)$user->otp) {
            return back()->withErrors(['otp' => 'Invalid OTP']);
        }

        if (Carbon::now()->gt($user->otp_expires_at)) {
            return back()->withErrors(['otp' => 'OTP expired']);
        }

        $user->update([
            'is_verified' => true,
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        Auth::login($user);
        session()->forget('otp_email');

        // ✅ Admin vs User redirect
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard'); // admin dashboard
        }

        return redirect()->route('user.dashboard'); // user dashboard
    }

    // ================= LOGIN FORM =================
    public function showLoginForm() {
        return view('auth.login');
    }

    // ================= LOGIN =================
    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return back()->withErrors(['email' => 'Invalid credentials']);
        }

        // Check OTP verification
        if (!Auth::user()->is_verified) {
            Auth::logout();
            return back()->withErrors(['email' => 'Please verify OTP first']);
        }

        // ✅ Redirect based on role
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('user.dashboard');
    }

    // ================= LOGOUT =================
    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.form');
    }
}
