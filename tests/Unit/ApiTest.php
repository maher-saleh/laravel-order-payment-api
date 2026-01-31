<?php

use Illuminate\Http\Request;

test('OrderResource transforms data correctly', function () {
    $order = new \App\Models\Order([
        'user_id' => 10,
        'status' => 'confirmed',
        'total_amount' => 150.50,
    ]);
    
    $order->setAttribute('id', 1);
    
    // Mock the items relationship
    $order->setRelation('items', collect([]));
    $order->setRelation('payments', collect([]));
    
    $resource = new \App\Http\Resources\OrderResource($order);
    $array = $resource->toArray(new Request());
    
    expect($array)->toHaveKey('id', 1);
    expect($array)->toHaveKey('status', 'confirmed');
    expect($array)->toHaveKey('total_amount', 150.50);
});

test('PaymentResource transforms data correctly', function () {
    $payment = new \App\Models\Payment([
        'order_id' => 1,
        'payment_method' => 'stripe',
        'status' => 'successful',
        'amount' => 99.99,
        'transaction_id' => 'txn_abc123',
    ]);

    $payment->setAttribute('id', 5);
    
    $resource = new \App\Http\Resources\PaymentResource($payment);
    $array = $resource->toArray(new Request());
    
    expect($array)->toHaveKey('id', 5);
    expect($array)->toHaveKey('payment_method', 'stripe');
    expect($array)->toHaveKey('status', 'successful');
    expect($array)->toHaveKey('amount', 99.99);
});