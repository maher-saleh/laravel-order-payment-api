<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    
    use HasFactory;


    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'order_id',
        'payment_method',
        'status',
        'amount',
        'gateway_response',
        'transaction_id',
    ];



    /**
     * Attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
    ];



    /**
     * Payment statuses.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESSFUL = 'successful';
    public const STATUS_FAILED = 'failed';



    /**
     * Get the order that owns the payment.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }



    /**
     * Mark payment as successful.
     */
    public function markAsSuccessful(array $gatewayResponse = []): self
    {
        $this->update([
            'status' => self::STATUS_SUCCESSFUL,
            'gateway_response' => $gatewayResponse,
        ]);

        return $this;
    }



    /**
     * Mark payment as failed.
     */
    public function markAsFailed(array $gatewayResponse = []): self
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'gateway_response' => $gatewayResponse,
        ]);

        return $this;
    }
    


    /**
     * Scope a query to only include successful payments.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCESSFUL);
    }
    


    /**
     * Scope a query to only include failed payments.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }
    


    /**
     * Scope a query to only include pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
}