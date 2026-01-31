<?php

use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentGatewayConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->token = auth('api')->login($this->user);
    
    // Seed payment gateway configurations directly
    PaymentGatewayConfig::create([
        'gateway_name' => 'credit_card',
        'config' => [
            'merchant_id' => 'MERCHANT_123456',
            'api_key' => 'test_api_key_credit_card',
            'environment' => 'sandbox',
        ],
        'is_active' => true,
    ]);

    PaymentGatewayConfig::create([
        'gateway_name' => 'paypal',
        'config' => [
            'client_id' => 'test_paypal_client_id',
            'client_secret' => 'test_paypal_client_secret',
            'mode' => 'sandbox',
        ],
        'is_active' => true,
    ]);

    PaymentGatewayConfig::create([
        'gateway_name' => 'stripe',
        'config' => [
            'publishable_key' => 'pk_test_123456789',
            'secret_key' => 'sk_test_987654321',
            'webhook_secret' => 'whsec_test_123',
        ],
        'is_active' => true,
    ]);
});

test('user can process payment for confirmed order', function () {
    $order = Order::factory()->confirmed()->create([
        'user_id' => $this->user->id,
        'total_amount' => 100.00,
    ]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->postJson("/api/orders/{$order->id}/payments", [
            'payment_method' => 'credit_card',
            'card_number' => '4111111111111111',
            'card_expiry' => '02/26',
            'card_cvv' => '123',
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'order_id',
                'payment_method',
                'status',
                'amount',
                'transaction_id',
            ],
        ]);
});

test('cannot process payment for pending order', function () {
    $order = Order::factory()->pending()->create([
        'user_id' => $this->user->id,
        'total_amount' => 100.00,
    ]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->postJson("/api/orders/{$order->id}/payments", [
            'payment_method' => 'credit_card',
            'card_number'    => '4242424242424242',
            'card_expiry'    => '12/28',
            'card_cvv'       => '123',
        ]);

    $response->assertStatus(422);
});

test('user can view payment details', function () {
    $order = Order::factory()->create(['user_id' => $this->user->id]);
    $payment = Payment::factory()->create(['order_id' => $order->id]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->getJson("/api/payments/{$payment->id}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $payment->id,
                'order_id' => $order->id,
                'payment_method' => $payment->payment_method,
            ],
        ]);
});

test('user can view all payments for an order', function () {
    $order = Order::factory()->create(['user_id' => $this->user->id]);
    Payment::factory()->count(3)->create(['order_id' => $order->id]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->getJson("/api/orders/{$order->id}/payments");

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(3);
});

test('user can view payment statistics', function () {
    $order = Order::factory()->create(['user_id' => $this->user->id]);
    
    Payment::factory()->successful()->create([
        'order_id' => $order->id,
        'amount' => 50.00,
    ]);
    
    Payment::factory()->failed()->create([
        'order_id' => $order->id,
        'amount' => 30.00,
    ]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->getJson("/api/orders/{$order->id}/payments/stats");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'total_payments' => 2,
                'successful_payments' => 1,
                'failed_payments' => 1,
                'total_paid' => 50.00,
            ],
        ]);
});

test('payment validation requires correct payment method', function () {
    $order = Order::factory()->confirmed()->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->postJson("/api/orders/{$order->id}/payments", [
            'payment_method' => 'invalid_gateway',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('payment_method');
});

test('credit card payment requires card details', function () {
    $order = Order::factory()->confirmed()->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->postJson("/api/orders/{$order->id}/payments", [
            'payment_method' => 'credit_card',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['card_number', 'card_expiry', 'card_cvv']);
});