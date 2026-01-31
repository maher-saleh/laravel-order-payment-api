<?php

use App\DTOs\PaymentResultDTO;

test('PaymentResultDTO creates successful result', function () {
    $result = PaymentResultDTO::success(
        message: 'Payment successful',
        transactionId: 'txn_123',
        gatewayResponse: ['status' => 'approved']
    );

    expect($result->success)->toBeTrue();
    expect($result->message)->toBe('Payment successful');
    expect($result->transactionId)->toBe('txn_123');
    expect($result->gatewayResponse)->toBe(['status' => 'approved']);
    expect($result->errorCode)->toBeNull();
});

test('PaymentResultDTO creates failure result', function () {
    $result = PaymentResultDTO::failure(
        message: 'Payment failed',
        errorCode: 'INSUFFICIENT_FUNDS',
        gatewayResponse: ['error' => 'declined']
    );

    expect($result->success)->toBeFalse();
    expect($result->message)->toBe('Payment failed');
    expect($result->errorCode)->toBe('INSUFFICIENT_FUNDS');
    expect($result->transactionId)->toBeNull();
});

test('PaymentResultDTO converts to array correctly', function () {
    $result = PaymentResultDTO::success('Test', 'txn_456');
    $array = $result->toArray();

    expect($array)->toBeArray();
    expect($array)->toHaveKey('success');
    expect($array)->toHaveKey('message');
    expect($array)->toHaveKey('transaction_id');
    expect($array['success'])->toBeTrue();
});