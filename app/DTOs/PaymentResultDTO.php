<?php

namespace App\DTOs;

/**
 * Data Transfer Object for payment processing results.
 * 
 * DTOs help us pass structured data between layers without
 * coupling to specific implementations.
 */
class PaymentResultDTO
{
    
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly ?string $transactionId = null,
        public readonly array $gatewayResponse = [],
        public readonly ?string $errorCode = null,
    ) {
    }



    /**
     * Cretae a successful payment result.
     */
    public static function success(
        string $message,
        ?string $transactionId = null,
        array $gatewayResponse = []
    ): self {
        return new self(
            success: true,
            message: $message,
            transactionId: $transactionId,
            gatewayResponse: $gatewayResponse,
        );
    }


    
    /**
     * Create a failed payment result.
     */
    public static function failure(
        string $message,
        ?string $errorCode = null,
        array $gatewayResponse = []
    ): self {
        return new self(
            success: false,
            message: $message,
            gatewayResponse: $gatewayResponse,
            errorCode: $errorCode,
        );
    }



    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'transaction_id' => $this->transactionId,
            'gateway_response' => $this->gatewayResponse,
            'error_code' => $this->errorCode,
        ];
    }
}