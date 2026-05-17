<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'method',
        'midtrans_token',
        'midtrans_transaction_id',
        'midtrans_payment_type',
        'proof_image',
        'status',
        'amount',
        'paid_at',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function getProofImageUrlAttribute(): ?string
    {
        return $this->proof_image
            ? asset('storage/' . $this->proof_image)
            : null;
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}