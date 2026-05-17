<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'recipient_name',
        'phone',
        'province',
        'province_id',
        'city',
        'city_id',
        'district',
        'postal_code',
        'address_detail',
        'courier',
        'service',
        'service_description',
        'estimated_days',
    ];

    public function getFullAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->address_detail,
            $this->district,
            $this->city,
            $this->province,
            $this->postal_code,
        ]));
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}