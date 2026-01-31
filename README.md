# Order and Payment Management API

A Laravel 12.x-based RESTful API for managing orders and payments with an extensible payment gateway system using the Strategy Pattern.

## Features

* Order Management: Create, read, update, and delete orders
* Payment Processing: Process payments through multiple gateways
* Extensible Architecture: Easy to add new payment gateways
* JWT Authentication: Secure API endpoints with JSON Web Tokens
* RESTful Design: Following REST principles and best practices
* Comprehensive Testing: Well-structured test suite with detailed coverage metrics
* Clean Code: PSR-12 standards, SOLID principles, and design patterns

## Requirements

* PHP 8.2 or higher
* Composer
* MySQL 8.0 or higher
* Laravel 12.x

## Installation

1. Clone the repository

```bash
git clone <repository-url>
cd laravel-order-payment-api
```

2. Install dependencies

```bash
composer install
```

3. Environment setup

```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

4. Configure database

Edit `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=order_payment_api
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. Run migrations and seeders

```bash
php artisan migrate
php artisan db:seed --class=PaymentGatewayConfigSeeder
```

6. Start the development server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

## Authentication

This API uses JWT (JSON Web Tokens) for authentication.

### Register a new user

```http
POST /api/auth/register
Content-Type: application/json

{
  "name": "Maher Saleh",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

### Login

```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

Response:

```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

### Using the token

Include the token in all authenticated requests:

```http
GET /api/orders
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

## API Endpoints

### Authentication

* POST /api/auth/register - Register new user
* POST /api/auth/login - Login user
* POST /api/auth/logout - Logout user
* POST /api/auth/refresh - Refresh token
* GET /api/auth/me - Get current user

### Orders

* GET /api/orders - List all orders (with pagination)
* POST /api/orders - Create new order
* GET /api/orders/{id} - Get order details
* PUT /api/orders/{id} - Update order
* DELETE /api/orders/{id} - Delete order
* POST /api/orders/{id}/confirm - Confirm order
* POST /api/orders/{id}/cancel - Cancel order

### Payments

* GET /api/payments - List all payments
* GET /api/payments/{id} - Get payment details
* POST /api/orders/{id}/payments - Process payment for order
* GET /api/orders/{id}/payments - Get payments for order
* GET /api/orders/{id}/payments/stats - Get payment statistics
* POST /api/payments/{id}/refund - Refund payment

***

## Adding New Payment Gateways – Developer Guide

### Overview

The payment system uses the **Strategy Pattern**, which allows adding new gateways without modifying existing code. Each gateway is a class implementing a common interface.

### Architecture

```
PaymentGatewayInterface (Contract)
        ↑
        |
AbstractPaymentGateway (Base Implementation)
        ↑
        |
   ┌────┴────┬─────────┬──────────┐
   |         |         |          |
Credit   PayPal   Stripe    YourNewGateway
Card
```

### Step 1: Create Gateway Class

Create `app/Services/PaymentGateways/YourGatewayName.php`:

```php
<?php

namespace App\Services\PaymentGateways;

use App\DTOs\PaymentResultDTO;
use App\Models\Payment;

class YourGatewayName extends AbstractPaymentGateway
{
    public function getName(): string
    {
        return 'your_gateway_name';
    }

    protected function validateConfiguration(): bool
    {
        return $this->getConfig('api_key') !== null
            && $this->getConfig('secret_key') !== null;
    }

    public function processPayment(Payment $payment): PaymentResultDTO
    {
        $this->log('info', 'Processing payment', ['payment_id' => $payment->id, 'amount' => $payment->amount]);

        try {
            $paymentData = $this->preparePaymentData($payment);
            $response = $this->callExternalAPI($paymentData);

            if ($response['success']) {
                $transactionId = $this->generateTransactionId();
                $this->log('info', 'Payment successful', ['payment_id' => $payment->id, 'transaction_id' => $transactionId]);

                return PaymentResultDTO::success(
                    message: 'Payment processed successfully',
                    transactionId: $transactionId,
                    gatewayResponse: $response
                );
            }

            $this->log('warning', 'Payment failed', ['payment_id' => $payment->id, 'reason' => $response['message']]);

            return PaymentResultDTO::failure(
                message: $response['message'],
                errorCode: $response['error_code'] ?? 'PAYMENT_FAILED',
                gatewayResponse: $response
            );

        } catch (\Exception $e) {
            $this->log('error', 'Payment exception', ['payment_id' => $payment->id, 'error' => $e->getMessage()]);

            return PaymentResultDTO::failure(
                message: 'Payment processing error: ' . $e->getMessage(),
                errorCode: 'GATEWAY_ERROR'
            );
        }
    }

    private function preparePaymentData(Payment $payment): array
    {
        return [
            'amount' => $payment->amount,
            'currency' => 'USD',
            'order_id' => $payment->order_id,
            'api_key' => $this->getConfig('api_key'),
        ];
    }

    private function callExternalAPI(array $data): array
    {
        return [
            'success' => true,
            'transaction_id' => uniqid('txn_'),
            'status' => 'approved',
        ];
    }

    public function refund(Payment $payment, ?float $amount = null): PaymentResultDTO
    {
        $refundAmount = $amount ?? $payment->amount;
        $this->log('info', 'Processing refund', ['payment_id' => $payment->id, 'refund_amount' => $refundAmount]);

        $transactionId = $this->generateTransactionId();

        return PaymentResultDTO::success(
            message: 'Refund processed successfully',
            transactionId: $transactionId,
            gatewayResponse: ['refund_amount' => $refundAmount, 'original_transaction' => $payment->transaction_id]
        );
    }
}
```

### Step 2: Register Gateway

Add to `PaymentGatewayManager.php`:

```php
protected array $gateways = [
    'credit_card' => \App\Services\PaymentGateways\CreditCardGateway::class,
    'paypal' => \App\Services\PaymentGateways\PayPalGateway::class,
    'stripe' => \App\Services\PaymentGateways\StripeGateway::class,
    'your_gateway_name' => \App\Services\PaymentGateways\YourGatewayName::class,
];
```

### Step 3: Add Configuration

Add gateway via database seeder or `.env`.

### Step 4: Update Validation

Update `ProcessPaymentRequest.php` for any new fields required by the gateway.

### Step 5: Create Tests

Add unit and feature tests in `tests/Feature/YourGatewayNameTest.php`.

### Step 6: Update Documentation

Update [README.md]() and Postman collection accordingly.

You can import the Postman collection here: [postman\_collection.json](postman/OrderPaymentAPI.postman_collection.json)

***

## Testing

**Summary:**

* **Total Tests:** 64
* **Assertions:** 191
* **Duration:** 4.70 seconds
* **Coverage:** 59.3%
* **Pass Rate:** 100%

### Coverage Details

* Authentication: 100%
* Business Logic: 100%
* Payment Gateway Interface: 100%
* DTOs & Exceptions: 100%
* Validation: 93%
* Controllers: 60%+
* Services: 74%

### Running Tests

```bash
php artisan test
php artisan test --coverage
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
php artisan test --coverage --min=50
```

For PCOV:

```bash
composer require --dev pcov/clobber
vendor/bin/pcov clobber
php -m | findstr pcov  # Windows
php -m | grep pcov     # Linux/Mac
```

For full test documentation see [TESTING\_DOCUMENTATION.md](TESTING_DOCUMENTATION.md).

***

## Exception Handling

All `/api/*` routes return JSON errors.

```json
{
  "message": "Human-readable error message",
  "status": 404
}
```

HTTP status codes:

* 200 - Success
* 201 - Created
* 401 - Unauthenticated
* 403 - Forbidden
* 404 - Not Found
* 422 - Validation Error
* 500 - Server Error
