<?php

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->token = auth('api')->login($this->user);
});

test('user can create an order', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->postJson('/api/orders', [
            'items' => [
                [
                    'product_name' => 'Product 1',
                    'quantity' => 2,
                    'price' => 10.50,
                ],
                [
                    'product_name' => 'Product 2',
                    'quantity' => 1,
                    'price' => 25.00,
                ],
            ],
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'user_id',
                'status',
                'total_amount',
                'items',
            ],
        ]);

    expect($response->json('data.total_amount'))->toBe('46.00');
    expect($response->json('data.status'))->toBe('pending');
});

test('user can view their orders', function () {
    // Create orders with items explicitly
    $orders = Order::factory()->count(3)->create(['user_id' => $this->user->id]);
    
    foreach ($orders as $order) {
        OrderItem::factory()->count(2)->create(['order_id' => $order->id]);
    }

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->getJson('/api/orders');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'status', 'total_amount', 'items'],
            ],
            'meta',
            'links',
        ]);

    expect($response->json('data'))->toHaveCount(3);
});

test('user can filter orders by status', function () {
    Order::factory()->create(['user_id' => $this->user->id, 'status' => 'confirmed']);
    Order::factory()->create(['user_id' => $this->user->id, 'status' => 'pending']);
    Order::factory()->create(['user_id' => $this->user->id, 'status' => 'cancelled']);

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->getJson('/api/orders?status=confirmed');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.status'))->toBe('confirmed');
});

test('user can update their order', function () {
    $order = Order::factory()->create(['user_id' => $this->user->id]);
    OrderItem::factory()->create(['order_id' => $order->id]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->putJson("/api/orders/{$order->id}", [
            'status' => 'confirmed',
        ]);

    $response->assertStatus(200);
    expect($response->json('data.status'))->toBe('confirmed');
});

test('user cannot view another users order', function () {
    $otherUser = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->getJson("/api/orders/{$order->id}");

    $response->assertStatus(403);
});

test('user can delete order without payments', function () {
    $order = Order::factory()->create(['user_id' => $this->user->id]);

    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->deleteJson("/api/orders/{$order->id}");

    $response->assertStatus(200);
    $this->assertSoftDeleted('orders', ['id' => $order->id]);
});

test('order requires at least one item', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->postJson('/api/orders', [
            'items' => [],
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('items');
});

test('order items must have valid data', function () {
    $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
        ->postJson('/api/orders', [
            'items' => [
                [
                    'product_name' => '',
                    'quantity' => 0,
                    'price' => -10,
                ],
            ],
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'items.0.product_name',
            'items.0.quantity',
            'items.0.price',
        ]);
});