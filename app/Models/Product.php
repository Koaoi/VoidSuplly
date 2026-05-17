<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, Sluggable;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'details',
        'price',
        'stock',
        'weight',
        'status',
        'is_limited',
        'release_date',
        'sizes',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'is_limited'   => 'boolean',
        'release_date' => 'datetime',
        'sizes'        => 'array',
    ];

    public function sluggable(): array
    {
        return [
            'slug' => ['source' => 'name'],
        ];
    }

    // ─── Accessors ───────────────────────────────────────────────────────────

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getPrimaryImageAttribute(): ?ProductImage
    {
        return $this->images()->where('is_primary', true)->first()
            ?? $this->images()->first();
    }

    public function getPrimaryImageUrlAttribute(): string
    {
        $image = $this->primary_image;
        return $image
            ? asset('storage/' . $image->image_path)
            : asset('images/product-placeholder.png');
    }

    public function getAverageRatingAttribute(): float
    {
        return $this->reviews()->where('is_approved', true)->avg('rating') ?? 0;
    }

    public function getIsReleasedAttribute(): bool
    {
        return is_null($this->release_date) || $this->release_date->isPast();
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')->where('stock', '>', 0);
    }

    public function scopeLimited($query)
    {
        return $query->where('is_limited', true);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function variants()
{
    return $this->hasMany(ProductVariant::class);
}

    public function reviews()
    {
        return $this->hasMany(Review::class)->where('is_approved', true)->latest();
    }
}