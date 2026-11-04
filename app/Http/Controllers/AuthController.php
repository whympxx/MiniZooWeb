<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Pesan error sengaja dibuat umum agar tidak membocorkan email mana yang terdaftar
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
        }

        $user = $request->user();

        if (! $user->is_active) {
            Auth::logout();

            return back()->withErrors(['email' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();
        $user->loginLogs()->create(['login_time' => now(), 'ip_address' => $request->ip()]);

        return redirect()->intended($user->isAdmin() ? route('admin.dashboard') : route('dashboard'));
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'phone' => ['required', 'regex:/^\d{10,20}$/'],
            'password' => ['required', 'confirmed', Password::min(6)->mixedCase()->numbers()],
            'terms' => ['accepted'],
        ], [
            'name.unique' => 'Username sudah dipakai.',
            'email.unique' => 'Email sudah terdaftar.',
            'phone.regex' => 'Nomor telepon minimal 10 digit angka.',
            'terms.accepted' => 'Anda harus menyetujui syarat & ketentuan.',
        ]);

        // Role selalu "user"; admin hanya bisa dibuat oleh admin lain
        User::create([
            'username' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'role' => 'user',
        ]);

        return redirect()->route('login')->with('status', 'Registrasi berhasil! Silakan login.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
