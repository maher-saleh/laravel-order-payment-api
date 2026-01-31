<?php

namespace App\Contracts;

use App\Models\Payment;
use App\DTOs\PaymentResultDTO;

/**
 * Interface that all payment gateways must implement.
 * 
 * This ensures consistency across all payment implementations
 * and makes it easy to add new gateways.
 */
interface PaymentGatewayInterface
{

    /**
     * Process a payment through the gateway.
     * 
     * @param Payment $payment The payment to process
     * @return PaymentResultDTO Result of the payment processing
     */
    public function processPayment(Payment $payment): PaymentResultDTO;



    /**
     * Get the gateway name.
     * 
     * @return string
     */
    public function getName(): string;



    /**
     * Validate gateway configuration.
     * 
     * @return bool
     */
    public function isConfigured(): bool;



    /**
     * Refund a payment (optional - can throw NotImplementedException).
     * 
     * @param Payment $payment
     * @param float|null $amount Partial refund amount, null for full refund
     * @return PaymentResultDTO
     */
    public function refund(Payment $payment, ?float $amount = null): PaymentResultDTO;
}