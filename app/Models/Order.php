<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Exceptions\OrderException;

class Order extends Model
{

    use HasFactory, SoftDeletes;


    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'user_id',
        'status',
        'total_amount',
    ];



    /**
     * Attributes that should be cast.
     */
    protected $casts = [
        'total_amount' => 'decimal:2',
    ];



    /**
     * Order statuses.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';



    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }



    /**
     * Get the order items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }



    /**
     * Get the order payments.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }



    /**
     * Scope a query to only include orders of a given status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }



    /**
     * Check if order can be deleted.
     * 
     * @throws OrderException
     */
    public function canBeDeleted(): bool
    {
        if($this->payments()->exists()) {
            throw new OrderException('Cannot delete order with associated payments');
        }
        
        return true;
    }



    /**
     * Check if order can accept payments.
     */
    public function canAcceptPayment(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }



    /**
     * Confirm the order.
     */
    public function confirm(): self
    {
        $this->update([
            'status' => self::STATUS_CONFIRMED
        ]);
        
        return $this;
    }



    /**
     * Cancel the order.
     */
    public function cancel(): self
    {
        $this->update([
            'status' => self::STATUS_CANCELLED
        ]);
        
        return $this;
    }



    /**
     * Calculate total from items.
     */
    public function calculateTotal(): float
    {
        return $this->items->sum(function($item){
            return $item->quantity * $item->price;
        });
    }


    
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function($order){
            $order->canBeDeleted();
        });
    }
}