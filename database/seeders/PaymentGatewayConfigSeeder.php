<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentGatewayConfig;

/**
 * Payment Gateway Config Seeder.
 * 
 * Seeds the database with example payment gateway configurations.
 * In production, manage these through admin panel or .env
 */
class PaymentGatewayConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gateways = [
            [
                'gateway_name' => 'credit_card',
                'config' => [
                    'merchant_id' => 'MERCHANT_123456',
                    'api_key' => 'test_api_key_credit_card',
                    'environment' => 'sandbox',
                ],
                'is_active' => true,
            ],
            [
                'gateway_name' => 'paypal',
                'config' => [
                    'client_id' => 'test_paypal_client_id',
                    'client_secret' => 'test_paypal_client_secret',
                    'mode' => 'sandbox',
                ],
                'is_active' => true,
            ],
            [
                'gateway_name' => 'stripe',
                'config' => [
                    'publishable_key' => 'pk_test_123456789',
                    'secret_key' => 'sk_test_987654321',
                    'webhook_secret' => 'whsec_test_123',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($gateways as $gateway) {
            PaymentGatewayConfig::updateOrCreate(
                ['gateway_name' => $gateway['gateway_name']],
                $gateway
            );
        }

        $this->command->info('Payment gateway configurations seeded successfully!');
    }
}