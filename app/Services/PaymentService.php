<?php

namespace App\Services;

use App\DTOs\PaymentResultDTO;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exceptions\PaymentException;

/**
 * Payment Service.
 * 
 * Handles the business logic for payment processing.
 * Coordinates between models, gateways, and database transactions.
 */
class PaymentService
{
    
    public function __construct(
        protected PaymentGatewayManager $gatewayManager
    ) {}



    /**
     * Process a payment for an order.
     * 
     * @param Order $order
     * @param string $paymentMethod Gateway name (credit_card, paypal, stripe)
     * @param array $paymentDetails Additional payment details
     * @return Payment
     * @throws PaymentException
     */
    public function processPayment(
        Order $order,
        string $paymentMethod,
        array $paymentDetails = []
    ): Payment {
        // Validate order can accept payment
        if(!$order->canAcceptPayment()){
            throw new PaymentException(
                "Cannot process payment for order with status: {$order->status}. " .
                "Order must be confirmed first."
            );
        }

        // Start database transaction
        return DB::transaction(function() use ($order, $paymentMethod, $paymentDetails){
            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method' => $paymentMethod,
                'status' => Payment::STATUS_PENDING,
                'amount' => $paymentDetails['amount'] ?? $order->total_amount,
            ]);

            try {
                // Get the appropriate gateway
                $gateway = $this->gatewayManager->gateway($paymentMethod);

                // Process payment through gateway
                $result = $gateway->processPayment($payment);

                // Update payment based on result
                if($result->success){
                    $payment->markAsSuccessful([
                        'transaction_id' => $result->transactionId,
                        'gateway_response' => $result->gatewayResponse,
                        'processed_at' => now(),
                    ]);

                    $payment->update([
                        'transaction_id' => $result->transactionId,
                    ]);

                    Log::info('Payment processed successfully', [
                        'payment_id' => $payment->id,
                        'order_id' => $order->ud,
                        'amount' => $payment->amount,
                    ]);
                } else {
                    $payment->markAsFailed([
                        'error_message' => $result->message,
                        'error_code' => $result->errorCode,
                        'gateway_response' => $result->gatewayResponse,
                        'failed_at' => now(),
                    ]);

                    Log::warning('Payment failed', [
                        'payment_id' => $payment->id, 
                        'order_id' => $order->id,
                        'reason' => $result->message,
                    ]);

                    throw new PaymentException($result->message);
                }

                return $payment->fresh();
            } catch(\Exception $e) {
                // Mark payment as failed
                $payment->markAsFailed([
                    'error_message' => $e->getMessage(),
                    'failed_at' => now(),
                ]);

                Log::error('Payment processing error', [
                    'payment_id' => $payment->id,
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);

                throw new PaymentException(
                    'Payment processing failed: ' . $e->getMessage(),
                    previous: $e
                );
            }
        });
    }



    /**
     * Refund a payment.
     * 
     * @param Payment $payment
     * @param float|null $amount Partial refund amunt
     * @return PaymentResultDTO
     */
    public function refundPayment(Payment $payment, ?float $amount = null): PaymentResultDTO
    {
        if($payment->status !== Payment::STATUS_SUCCESSFUL){
            throw new PaymentException('Can only refund successful payments');
        }

        $gateway = $this->gatewayManager->gateway($payment->payment_method);

        return $gateway->refund($payment, $amount);
    }



    /**
     * Get payment statistics for an order.
     * 
     * @param Order $order
     * @return array
     */
    public function getOrderPaymentStats(Order $order): array
    {
        $payments = $order->payments;

        return [
            'total_payments' => $payments->count(),
            'successful_payments' => $payments->where('status', Payment::STATUS_SUCCESSFUL)->count(),
            'failed_payments' => $payments->where('status', Payment::STATUS_FAILED)->count(),
            'pending_payments' => $payments->where('status', Payment::STATUS_PENDING)->count(),
            'total_paid' => $payments->where('status', Payment::STATUS_SUCCESSFUL)->sum('amount'),
            'total_pending' => $payments->where('status', Payment::STATUS_PENDING)->sum('amount'),
        ];
    }
}