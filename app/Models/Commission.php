<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Commission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'reference_image',
        'product_type',
        'budget',
        'quantity',
        'status',
        'admin_note',
        'quoted_price',
    ];

    protected $casts = [
        'budget'       => 'decimal:2',
        'quoted_price' => 'decimal:2',
    ];

    public static array $statusLabels = [
        'pending'     => 'Menunggu Review',
        'reviewing'   => 'Sedang Ditinjau',
        'accepted'    => 'Diterima',
        'in_progress' => 'Sedang Dikerjakan',
        'rejected'    => 'Ditolak',
        'completed'   => 'Selesai',
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::$statusLabels[$this->status] ?? $this->status;
    }

    public function getReferenceImageUrlAttribute(): ?string
    {
        return $this->reference_image
            ? asset('storage/' . $this->reference_image)
            : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}