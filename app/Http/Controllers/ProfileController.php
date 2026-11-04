<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('pages.profil', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'username' => ['nullable', 'string', 'min:3', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'old_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', Password::min(6)->mixedCase()->numbers()],
            'profile_picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:5120'],
        ], [
            'old_password.required_with' => 'Masukkan password lama untuk mengganti password.',
            'old_password.current_password' => 'Password lama salah.',
            'profile_picture.image' => 'Format foto tidak didukung.',
            'profile_picture.mimes' => 'Format foto tidak didukung.',
        ]);

        if (! empty($data['username'])) {
            $user->username = $data['username'];
        }

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $user->profile_picture = $request->file('profile_picture')->store('profile-pictures', 'public');
        }

        $user->save();

        return redirect()->route('profil')->with('update_success', 'Profil berhasil diperbarui!');
    }
}
