<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'size',
        'quantity',
        'price', // TAMBAHKAN - penting untuk menyimpan harga saat checkout
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'integer',
    ];

    protected $appends = [
        'subtotal',
        'formatted_subtotal',
        'formatted_price',
    ];

    // ─── Relasi ───────────────────────────────────────────────────────────────

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    // ─── Accessors (getters) ─────────────────────────────────────────────────

    /**
     * Get product price (dari item atau fallback ke product)
     */
    public function getEffectivePriceAttribute(): int
    {
        // Jika price di cart_item ada dan > 0, gunakan itu
        if ($this->price && $this->price > 0) {
            return (int) $this->price;
        }
        
        // Fallback ke harga product
        if ($this->product && $this->product->price > 0) {
            return (int) $this->product->price;
        }
        
        return 0;
    }

    /**
     * Get subtotal (price * quantity)
     */
    public function getSubtotalAttribute(): int
    {
        return $this->effective_price * $this->quantity;
    }

    /**
     * Get formatted subtotal (Rp xxx)
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    /**
     * Get formatted price (Rp xxx)
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->effective_price, 0, ',', '.');
    }

    /**
     * Get original price (dari product)
     */
    public function getOriginalPriceAttribute(): int
    {
        return $this->product ? (int) $this->product->price : 0;
    }

    // ─── Mutators (setters) ──────────────────────────────────────────────────

    /**
     * Set price saat create/update
     */
    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = (int) $value;
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    /**
     * Scope untuk item dengan size tertentu
     */
    public function scopeWithSize($query, $size)
    {
        if ($size) {
            return $query->where('size', $size);
        }
        return $query;
    }

    /**
     * Scope untuk product tertentu
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    // ─── Helper Methods ──────────────────────────────────────────────────────

    /**
     * Update price from product (jika harga product berubah)
     */
    public function syncPrice(): bool
    {
        if ($this->product && $this->product->price > 0) {
            $this->price = $this->product->price;
            return $this->save();
        }
        return false;
    }

    /**
     * Increment quantity
     */
    public function incrementQuantity(int $amount = 1): void
    {
        $this->increment('quantity', $amount);
    }

    /**
     * Decrement quantity (akan hapus jika hasilnya 0)
     */
    public function decrementQuantity(int $amount = 1): ?bool
    {
        $newQuantity = $this->quantity - $amount;
        
        if ($newQuantity <= 0) {
            return $this->delete();
        }
        
        return $this->update(['quantity' => $newQuantity]);
    }

    /**
     * Check if item is available (stock check)
     */
    public function isAvailable(): bool
    {
        if (!$this->product) {
            return false;
        }
        
        if ($this->product->status !== 'available') {
            return false;
        }
        
        return $this->product->stock >= $this->quantity;
    }

    /**
     * Get stock warning message
     */
    public function getStockWarningAttribute(): ?string
    {
        if (!$this->product || $this->product->status !== 'available') {
            return null;
        }
        
        if ($this->quantity > $this->product->stock) {
            return "Stok tidak mencukupi. Tersisa {$this->product->stock} pcs.";
        }
        
        if ($this->product->stock <= 5) {
            return "Sisa stok {$this->product->stock} pcs!";
        }
        
        return null;
    }
}