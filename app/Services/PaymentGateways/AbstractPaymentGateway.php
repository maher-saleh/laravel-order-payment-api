<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\PaymentGatewayConfig;
use App\DTOs\PaymentResultDTO;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Abstract base class for payment gateways.
 * 
 * Provides common functionality that all gateways can use,
 * reducing code duplication.
 */
abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{

    protected ?PaymentGatewayConfig $config = null;



    /**
     * Constructor loads configuration from database.
     */
    public function __construct()
    {
        $this->loadConfiguration();
    }



    /**
     * Load gateway configuration from database.
     */
    protected function loadConfiguration(): void
    {
        $this->config = PaymentGatewayConfig::where('gateway_name', $this->getName())
             ->where('is_active', true)
             ->first();
    }



    /**
     * Check if gateway is properly configured.
     */
    public function isConfigured(): bool
    {
        return $this->config !== null && $this->validateConfiguration();
    }



    /**
     * Validate specific gateway configuration.
     * Override in child classes for custom validation.
     */
    abstract protected function validateConfiguration(): bool;



    /**
     * Get configuration value.
     */
    protected function getConfig(string $key, $default = null)
    {
        return $this->config?->getConfigValue($key, $default);
    }



    /**
     * Log gateway activity.
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        Log::log($level, "[{$this->getName()}] $message", $context);
    }



    /**
     * Default refund implementation (can be overridden).
     */
    public function refund(Payment $payment, ?float $amount = null): PaymentResultDTO
    {
        throw new \Exception("Refund not implemented for {$this->getName()}");
    }



    /**
     * Generate a unique transaction ID.
     */
    protected function generateTransactionId(): string
    {
        // Using ordered UUID to ensure uniqueness and avoid time-based collisions
        return $this->getName() . '_' . Str::orderedUuid();
    }
}