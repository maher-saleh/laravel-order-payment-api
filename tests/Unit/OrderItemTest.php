<?php

test('OrderItem calculates subtotal correctly', function () {
    $item = new \App\Models\OrderItem([
        'product_name' => 'Test Product',
        'quantity' => 3,
        'price' => 25.50,
    ]);
    
    expect($item->subtotal)->toBe(76.50);
});

test('OrderItem subtotal handles decimal precision', function () {
    $item = new \App\Models\OrderItem([
        'product_name' => 'Test Product',
        'quantity' => 2,
        'price' => 10.99,
    ]);
    
    expect($item->subtotal)->toBe(21.98);
});