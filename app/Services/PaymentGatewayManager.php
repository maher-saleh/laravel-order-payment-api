<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\App;

use App\Exceptions\PaymentGatewayException;

/**
 * Payment Gateway Manager.
 * 
 * This is the "Context" in the Strategy Pattern.
 * It manages all available gateways and selects the appropriate one.
 * 
 * Benefits:
 * - Centralized gateway management
 * - Easy to add new gateways (just register them here)
 * - Consistent interface for all payment processing
 */
class PaymentGatewayManager
{
    
    /**
     * Registered payment gateways.
     * 
     * Add new gateways here in the format:
     * 'gateway_name' => GatewayClass::class
     */
    protected array $gateways = [
        'credit_card' => \App\Services\PaymentGateways\CreditCardGateway::class,
        'paypal' => \App\Services\PaymentGateways\PayPalGateway::class,
        'stripe' => \App\Services\PaymentGateways\StripeGateway::class,
    ];



    /**
     * Cached gateway instances.
     */
    protected array $instances = [];



    /**
     * Get a payment gateway instance by name.
     * 
     * @param string $gatewayName
     * @return PaymentGatewayInterface
     * @throws PaymentGatewayException
     */
    public function gateway(string $gatewayName): PaymentGatewayInterface
    {
        // Return cached instance if available
        if(isset($this->instances[$gatewayName])){
            return $this->instances[$gatewayName];
        }

        // Check if gateway is registered
        if(!isset($this->gateways[$gatewayName])){
            throw new PaymentGatewayException(
                "Payment gateway '{$gatewayName}' is not registered. " .
                "Available gateways: " . implode(', ', $this->available())
            );
        }

        // Create gateway instance
        $gatewayClass = $this->gateways[$gatewayName];
        $gateway = App::make($gatewayClass);

        // Verify it implements the correct interface
        if(!$gateway instanceof PaymentGatewayInterface){
            throw new PaymentGatewayException(
                'Gateway class must implement PaymentGatewayInterface'
            );
        }

        // Check if gateway is configured
        if(!$gateway->isConfigured()){
            throw new PaymentGatewayException(
                "Payment gateway '{$gatewayName}' is not properly configured"
            );
        }

        // Cache and return
        $this->instances[$gatewayName] = $gateway;
        return $gateway;
    }



    /**
     * Register a new payment gateway.
     * 
     * This method allows dynamic gateway registration,
     * useful for packages or plugins.
     * 
     * @param string $name Gateway identifier
     * @param string $class Gateway class name
     */
    public function register(string $name, string $class): void
    {
        $this->gateways[$name] = $class;
        
        // Clear cached instance if exists
        unset($this->instances[$name]);
    }



    /**
     * Get all registered gateway names.
     * 
     * @return array
     */
    public function available(): array
    {
        return array_keys($this->gateways);
    }



    /**
     * Check if a gateway is registered.
     * 
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->gateways[$name]);
    }



    /**
     * Get all configured and active gateways.
     * 
     * @return array
     */
    public function getConfiguredGateways(): array
    {
        $configured = [];

        foreach($this->gateways as $name => $class) {
            try {
                $gateway = App::make($class);
                if($gateway->isConfigured()){
                    $configured[] = $name;
                }
            } catch (\Exception $e) {
                // Skip gateways that can't be instantiated
                continue;
            }
        }

        return $configured;
    }
}