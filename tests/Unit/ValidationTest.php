<?php

test('StoreOrderRequest has correct validation rules', function () {
    $request = new \App\Http\Requests\StoreOrderRequest();
    $rules = $request->rules();
    
    expect($rules)->toHaveKey('items');
    expect($rules)->toHaveKey('items.*.product_name');
    expect($rules)->toHaveKey('items.*.quantity');
    expect($rules)->toHaveKey('items.*.price');
    
    expect($rules['items'])->toContain('required', 'array');
});

test('ProcessPaymentRequest validates payment method', function () {
    $request = new \App\Http\Requests\ProcessPaymentRequest();
    $request->setContainer(app());
    
    $rules = $request->rules();
    
    expect($rules)->toHaveKey('payment_method');
    expect($rules['payment_method'])->toContain('required', 'string');
});