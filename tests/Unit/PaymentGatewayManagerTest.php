<?php
use App\Services\PaymentGatewayManager;
use App\Services\PaymentGateways\CreditCardGateway;
use App\Exceptions\PaymentGatewayException;

test('PaymentGatewayManager registers gateways correctly', function () {
    $manager = new PaymentGatewayManager();
    
    $available = $manager->available();
    
    expect($available)->toBeArray();
    expect($available)->toContain('credit_card', 'paypal', 'stripe');
});

test('PaymentGatewayManager checks if gateway exists', function () {
    $manager = new PaymentGatewayManager();
    
    expect($manager->has('credit_card'))->toBeTrue();
    expect($manager->has('paypal'))->toBeTrue();
    expect($manager->has('nonexistent'))->toBeFalse();
});

test('PaymentGatewayManager can register new gateway at runtime', function () {
    $manager = new PaymentGatewayManager();
    
    // Register a test gateway
    $manager->register('test_gateway', CreditCardGateway::class);
    
    expect($manager->has('test_gateway'))->toBeTrue();
    expect($manager->available())->toContain('test_gateway');
});

test('PaymentGatewayManager throws exception for unregistered gateway', function () {
    $manager = new PaymentGatewayManager();
    
    expect(fn() => $manager->gateway('unknown_gateway'))
        ->toThrow(PaymentGatewayException::class, 'not registered');
});