<?php

test('Order has correct status constants', function () {
    expect(\App\Models\Order::STATUS_PENDING)->toBe('pending');
    expect(\App\Models\Order::STATUS_CONFIRMED)->toBe('confirmed');
    expect(\App\Models\Order::STATUS_CANCELLED)->toBe('cancelled');
});

test('Order canAcceptPayment returns true for confirmed status', function () {
    $order = new \App\Models\Order();
    $order->status = \App\Models\Order::STATUS_CONFIRMED;
    
    expect($order->canAcceptPayment())->toBeTrue();
});

test('Order canAcceptPayment returns false for pending status', function () {
    $order = new \App\Models\Order();
    $order->status = \App\Models\Order::STATUS_PENDING;
    
    expect($order->canAcceptPayment())->toBeFalse();
});

test('Order canAcceptPayment returns false for cancelled status', function () {
    $order = new \App\Models\Order();
    $order->status = \App\Models\Order::STATUS_CANCELLED;
    
    expect($order->canAcceptPayment())->toBeFalse();
});