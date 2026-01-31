<?php

use App\Services\PaymentGateways\CreditCardGateway;
use App\Services\PaymentGateways\PayPalGateway;
use App\Services\PaymentGateways\StripeGateway;

test('CreditCardGateway returns correct name', function () {
    // Create a mock config to avoid database
    $mockConfig = Mockery::mock('App\Models\PaymentGatewayConfig');
    $mockConfig->shouldReceive('where')->andReturnSelf();
    $mockConfig->shouldReceive('first')->andReturn(null);
    
    $gateway = new CreditCardGateway();
    
    expect($gateway->getName())->toBe('credit_card');
});

test('PayPalGateway returns correct name', function () {
    $gateway = new PayPalGateway();
    expect($gateway->getName())->toBe('paypal');
});

test('StripeGateway returns correct name', function () {
    $gateway = new StripeGateway();
    expect($gateway->getName())->toBe('stripe');
});

// Clean up mocks
afterEach(function () {
    Mockery::close();
});