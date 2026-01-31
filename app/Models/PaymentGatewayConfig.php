<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGatewayConfig extends Model
{
    
    use HasFactory;


    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'gateway_name',
        'config',
        'is_active',
    ];



    /**
     * Attributes that should be cast.
     */
    protected $casts = [
        'config' => 'encrypted:array',
        'is_active' => 'boolean',
    ];



    /**
     * Scope of query to only include active gateways.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }



    /**
     * Get a specific configuration value.
     */
    public function getConfigValue(string $key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }
}