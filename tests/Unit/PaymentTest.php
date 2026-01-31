<?php

use App\Models\Payment;

test('Payment model has correct status constants', function () {
    expect(Payment::STATUS_PENDING)->toBe('pending');
    expect(Payment::STATUS_SUCCESSFUL)->toBe('successful');
    expect(Payment::STATUS_FAILED)->toBe('failed');
});

test('Payment can be marked as successful without database', function () {
    // Create a mock payment (not saved to database)
    $payment = new Payment([
        'order_id' => 1,
        'payment_method' => 'credit_card',
        'status' => Payment::STATUS_PENDING,
        'amount' => 100.00,
    ]);
    
    // Test the status change logic
    expect($payment->status)->toBe('pending');
    $payment->status = Payment::STATUS_SUCCESSFUL;
    expect($payment->status)->toBe('successful');
});