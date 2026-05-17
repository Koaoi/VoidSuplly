<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect ke halaman login Google.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle callback dari Google OAuth.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Login Google gagal. Silakan coba lagi.');
        }

        // Cari user berdasarkan google_id atau email
        $user = User::where('google_id', $googleUser->id)
                    ->orWhere('email', $googleUser->email)
                    ->first();

        if ($user) {
            // User sudah ada — update google_id & avatar jika belum tersimpan
            $user->update([
                'google_id' => $googleUser->id,
                'avatar'    => $user->avatar ?? $googleUser->avatar,
            ]);
        } else {
            // User baru — buat akun baru
            $user = User::create([
                'name'              => $googleUser->name,
                'email'             => $googleUser->email,
                'google_id'         => $googleUser->id,
                'avatar'            => $googleUser->avatar,
                'password'          => null, // tidak pakai password native
                'role'              => 'customer',
                'email_verified_at' => now(),
            ]);

            // Buat cart otomatis
            Cart::create(['user_id' => $user->id]);
        }

        // Pastikan cart ada untuk user lama
        if (!$user->cart) {
            Cart::create(['user_id' => $user->id]);
        }

        Auth::login($user, true); // remember = true

        return redirect()->route('home')
            ->with('success', 'Berhasil login dengan Google. Selamat datang, ' . $user->name . '!');
    }
}