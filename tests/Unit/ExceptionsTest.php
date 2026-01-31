<?php

test('PaymentException can be thrown and caught', function () {
    expect(fn() => throw new \App\Exceptions\PaymentException('Test error'))
        ->toThrow(\App\Exceptions\PaymentException::class, 'Test error');
});

test('OrderException can be thrown and caught', function () {
    expect(fn() => throw new \App\Exceptions\OrderException('Order error'))
        ->toThrow(\App\Exceptions\OrderException::class, 'Order error');
});

test('PaymentGatewayException can be thrown and caught', function () {
    expect(fn() => throw new \App\Exceptions\PaymentGatewayException('Gateway error'))
        ->toThrow(\App\Exceptions\PaymentGatewayException::class, 'Gateway error');
});