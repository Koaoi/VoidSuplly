<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'order_id',
        'rating',
        'comment',
        'image',
        'is_approved',
        'admin_reply',              // ⭐ TAMBAHKAN
        'admin_reply_updated_at',   // ⭐ TAMBAHKAN
        'admin_reply_by',           // ⭐ TAMBAHKAN (opsional)
    ];

    protected $casts = [
        'rating'      => 'integer',
        'is_approved' => 'boolean',
        'admin_reply_updated_at' => 'datetime',
    ];

    public function getStarsAttribute(): string
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    // ⭐ RELASI KE ADMIN YANG MEMBALAS
    public function adminReply()
    {
        return $this->belongsTo(User::class, 'admin_reply_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}