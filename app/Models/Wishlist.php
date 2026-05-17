<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ─── Relasi ───────────────────────────────────────────────────────────────

    /**
     * Relasi ke User (pemilik wishlist)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Product (produk yang di-wishlist)
     * withTrashed() agar produk yang sudah dihapus soft-delete tetap terlihat di wishlist
     */
    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    /**
     * Scope untuk wishlist user tertentu
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope untuk produk tertentu
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    // ─── Helper Methods ───────────────────────────────────────────────────────

    /**
     * Cek apakah user sudah menambahkan product ke wishlist
     */
    public static function isInWishlist($userId, $productId): bool
    {
        return self::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Hitung jumlah wishlist user
     */
    public static function countByUser($userId): int
    {
        return self::where('user_id', $userId)->count();
    }

    /**
     * Toggle wishlist (tambah jika belum, hapus jika sudah)
     */
    public static function toggle($userId, $productId): array
    {
        $wishlist = self::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            $isWishlisted = false;
            $message = 'Dihapus dari wishlist';
        } else {
            self::create([
                'user_id' => $userId,
                'product_id' => $productId
            ]);
            $isWishlisted = true;
            $message = 'Ditambahkan ke wishlist';
        }

        return [
            'success' => true,
            'in_wishlist' => $isWishlisted,
            'message' => $message,
            'count' => self::countByUser($userId)
        ];
    }
}