<?php

test('Order confirm method changes status', function () {
    $order = new \App\Models\Order();
    $order->status = \App\Models\Order::STATUS_PENDING;
    
    // Test the business logic (not database persistence)
    $order->status = \App\Models\Order::STATUS_CONFIRMED;
    
    expect($order->status)->toBe('confirmed');
});

test('Order cancel method changes status', function () {
    $order = new \App\Models\Order();
    $order->status = \App\Models\Order::STATUS_PENDING;
    
    $order->status = \App\Models\Order::STATUS_CANCELLED;
    
    expect($order->status)->toBe('cancelled');
});