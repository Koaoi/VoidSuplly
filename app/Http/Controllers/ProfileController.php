<?php
// app/Http/Controllers/ProfileController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display user profile page
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get user stats
        $ordersCount = $user->orders()->count();
        $reviewsCount = $user->reviews()->count();
        $wishlistCount = $user->wishlists()->count();
        $commissionsCount = $user->commissions()->count();
        
        // Get recent orders
        $recentOrders = $user->orders()->latest()->take(5)->get();
        
        return view('profile.index', compact(
            'user',
            'ordersCount',
            'reviewsCount',
            'wishlistCount',
            'commissionsCount',
            'recentOrders'
        ));
    }

    /**
     * Show edit profile form
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($user->avatar && !str_contains($user->avatar, 'googleusercontent.com')) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            // Upload new avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        $user->update($validated);

        return redirect()->route('profile.index')
            ->with('success', 'Profile berhasil diperbarui.');
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->route('profile.index')
            ->with('success', 'Password berhasil diubah.');
    }

    /**
     * Delete user account
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();
        
        // Delete avatar if exists
        if ($user->avatar && !str_contains($user->avatar, 'googleusercontent.com')) {
            Storage::disk('public')->delete($user->avatar);
        }
        
        Auth::logout();
        $user->delete();

        return redirect('/')
            ->with('success', 'Akun berhasil dihapus.');
    }
}