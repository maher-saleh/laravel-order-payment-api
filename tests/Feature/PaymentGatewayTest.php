<?php

use App\Services\PaymentGatewayManager;
use App\Services\PaymentGateways\CreditCardGateway;
use App\Services\PaymentGateways\PayPalGateway;
use App\Services\PaymentGateways\StripeGateway;
use App\Exceptions\PaymentGatewayException;
use App\Models\PaymentGatewayConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\PaymentGatewayConfigSeeder;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => PaymentGatewayConfigSeeder::class]);
    $this->manager = new PaymentGatewayManager();
});

test('gateway manager returns correct gateway instance', function () {
    $gateway = $this->manager->gateway('credit_card');
    
    expect($gateway)->toBeInstanceOf(CreditCardGateway::class);
});

test('gateway manager returns different gateway instances', function () {
    $creditCard = $this->manager->gateway('credit_card');
    $paypal = $this->manager->gateway('paypal');
    $stripe = $this->manager->gateway('stripe');
    
    expect($creditCard)->toBeInstanceOf(CreditCardGateway::class);
    expect($paypal)->toBeInstanceOf(PayPalGateway::class);
    expect($stripe)->toBeInstanceOf(StripeGateway::class);
});

test('gateway manager caches instances', function () {
    $gateway1 = $this->manager->gateway('credit_card');
    $gateway2 = $this->manager->gateway('credit_card');
    
    expect($gateway1)->toBe($gateway2);
});

test('gateway manager throws exception for unknown gateway', function () {
    $this->manager->gateway('unknown_gateway');
})->throws(PaymentGatewayException::class, 'not registered');

test('gateway manager can register new gateway', function () {
    $this->manager->register('test_gateway', CreditCardGateway::class);
    
    expect($this->manager->has('test_gateway'))->toBeTrue();
    expect($this->manager->available())->toContain('test_gateway');
});

test('gateway manager lists all available gateways', function () {
    $available = $this->manager->available();
    
    expect($available)->toContain('credit_card', 'paypal', 'stripe');
});

test('gateway manager lists configured gateways', function () {
    $configured = $this->manager->getConfiguredGateways();
    
    expect($configured)->toContain('credit_card', 'paypal', 'stripe');
});

test('gateways have correct names', function ($gatewayName, $gatewayClass) {
    $gateway = app($gatewayClass);
    
    expect($gateway->getName())->toBe($gatewayName);
})->with([
    ['credit_card', CreditCardGateway::class],
    ['paypal', PayPalGateway::class],
    ['stripe', StripeGateway::class],
]);

test('gateways are properly configured', function ($gatewayName) {
    $gateway = $this->manager->gateway($gatewayName);
    
    expect($gateway->isConfigured())->toBeTrue();
})->with(['credit_card', 'paypal', 'stripe']);

test('gateway processes payment and returns result DTO', function () {
    $gateway = $this->manager->gateway('credit_card');
    $payment = \App\Models\Payment::factory()->create([
        'payment_method' => 'credit_card',
        'amount' => 100.00,
    ]);
    
    $result = $gateway->processPayment($payment);
    
    expect($result)->toBeInstanceOf(\App\DTOs\PaymentResultDTO::class);
    expect($result->success)->toBeIn([true, false]);
    expect($result->message)->toBeString();
});