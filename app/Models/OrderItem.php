<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    
    use HasFactory;


    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'order_id',
        'product_name',
        'quantity',
        'price',
    ];



    /**
     * Attributes that should be cast.
     */
    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];



    /**
     * Get the order that owns the item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }



    /**
     * Get item subtotal.
     */
    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->price;
    }
}