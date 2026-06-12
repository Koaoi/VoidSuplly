<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'order_code',
        'subtotal',
        'shipping_cost',
        'total_price',
        'status',
        'notes',
    ];

    protected $casts = [
        'subtotal'      => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_price'   => 'decimal:2',
    ];

    // Status labels untuk tampilan UI
    public static array $statusLabels = [
        'pending'     => 'Menunggu Pembayaran',
        'paid'        => 'Pembayaran Dikonfirmasi',
        'processing'  => 'Sedang Diproses',
        'shipped'     => 'Dalam Pengiriman',
        'completed'   => 'Selesai',
        'cancelled'   => 'Dibatalkan',
    ];

    public static array $statusColors = [
        'pending'    => 'yellow',
        'paid'       => 'blue',
        'processing' => 'purple',
        'shipped'    => 'orange',
        'completed'  => 'green',
        'cancelled'  => 'red',
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::$statusLabels[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::$statusColors[$this->status] ?? 'gray';
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class)->with('product');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function shippingAddress()
    {
        return $this->hasOne(ShippingAddress::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Relasi ke Commission (Many-to-Many melalui tabel order_commission)
     * Komisi yang dibeli dalam order ini
     */
    public function commissions()
    {
        return $this->belongsToMany(Commission::class, 'order_commission')
                    ->withPivot('quantity', 'price', 'subtotal', 'commission_rate')
                    ->withTimestamps();
    }

    /**
     * Helper untuk mendapatkan total komisi
     */
    public function getTotalCommissionAttribute(): float
    {
        if ($this->relationLoaded('commissions')) {
            return (float) $this->commissions->sum(function($item) {
                return (float) ($item->pivot->subtotal ?? 0);
            });
        }
        
        // Fallback jika relasi belum di-load
        return (float) $this->commissions()->sum('subtotal');
    }

    /**
     * Format total komisi ke Rupiah
     */
    public function getFormattedTotalCommissionAttribute(): string
    {
        return 'Rp ' . number_format($this->total_commission, 0, ',', '.');
    }

    /**
     * Mendapatkan semua item order (produk) saja, tanpa komisi
     */
    public function productItems()
    {
        return $this->hasMany(OrderItem::class)->whereNull('commission_id')->with('product');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public static function generateCode(): string
    {
        $date = now()->format('Ymd');
        $rand = strtoupper(substr(uniqid(), -4));
        return "VOID-{$date}-{$rand}";
    }
}