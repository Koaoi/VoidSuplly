<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',          // ✅ Tambahkan nomor telepon
        'password',
        'google_id',
        'avatar',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    // ─── Helpers ────────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && str_starts_with($this->avatar, 'http')) {
            return $this->avatar; // URL Google
        }

        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        // Gravatar fallback
        return 'https://www.gravatar.com/avatar/' . md5(strtolower($this->email)) . '?d=mp&s=200';
    }

    /**
     * Format nomor telepon
     */
    public function getFormattedPhoneAttribute(): string
    {
        if (!$this->phone) {
            return '-';
        }
        
        $phone = $this->phone;
        
        // Cek apakah sudah pakai kode negara
        if (str_starts_with($phone, '62')) {
            return '+' . $phone;
        }
        
        // Jika mulai dengan 0, ganti jadi +62
        if (str_starts_with($phone, '0')) {
            return '+62' . substr($phone, 1);
        }
        
        return $phone;
    }

    /**
     * Cek apakah user memiliki nomor telepon
     */
    public function hasPhone(): bool
    {
        return !empty($this->phone);
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class)->latest();
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class)->latest();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->latest();
    }
}