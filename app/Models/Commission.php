<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'product_type',
        'description',
        'quantity',
        'budget',
        'quoted_price',
        'reference_image',
        'status',
        'admin_note',
        'order_id',
    ];

    protected $casts = [
        'budget' => 'decimal:0',
        'quoted_price' => 'decimal:0',
        'quantity' => 'integer',
    ];

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Menunggu Review',
            'reviewing' => 'Sedang Direview',
            'accepted' => 'Disetujui',
            'in_progress' => 'Pengerjaan',
            'completed' => 'Selesai',
            'rejected' => 'Ditolak',
            'paid' => 'Dibayar',
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getProductTypeLabelAttribute()
    {
        $labels = [
            'hoodie' => 'Hoodie',
            'tshirt' => 'T-Shirt',
            'jersey' => 'Jersey',
            'jacket' => 'Jacket',
            'pants' => 'Pants',
            'totebag' => 'Tote Bag',
            'other' => 'Lainnya',
        ];
        return $labels[$this->product_type] ?? ucfirst($this->product_type);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getReferenceImageUrlAttribute()
    {
        if ($this->reference_image) {
            return Storage::url($this->reference_image);
        }
        return null;
    }

    public function getFormattedQuotedPriceAttribute()
    {
        if ($this->quoted_price) {
            return 'Rp ' . number_format($this->quoted_price, 0, ',', '.');
        }
        return '-';
    }

    public function getFormattedBudgetAttribute()
    {
        if ($this->budget) {
            return 'Rp ' . number_format($this->budget, 0, ',', '.');
        }
        return '-';
    }
}