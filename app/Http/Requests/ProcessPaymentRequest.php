<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\PaymentGatewayManager;

class ProcessPaymentRequest extends FormRequest
{
    
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // User can only process payment for their own orders
        return $this->user()->id === $this->route('order')->user_id;
    }



    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $gatewayManager = app(PaymentGatewayManager::class);
        $availableGateways = $gatewayManager->available();
        
        $method = $this->input('payment_method');

        $rules = [
            'payment_method' => [
                'required',
                'string',
                'in:' . implode(',', $availableGateways)
            ],
            'amount' => ['sometimes', 'numeric', 'min:0.01', 'max:999999.99'],
        ];

        // Gateway-specific rules
        if ($method === 'credit_card') {
            $rules += [
                'card_number' => ['required', 'string', 'digits_between:13,19'],
                'card_expiry' => ['required', 'string', 'date_format:m/y', 'after:today'],
                'card_cvv'    => ['required', 'string', 'size:3'],
            ];
        }

        if ($method === 'stripe') {
            $rules['payment_method_id'] = ['required', 'string'];  // or 'starts_with:pm_'
            // Optionally prohibit card fields
            $rules['card_number'] = ['prohibited'];
            $rules['card_expiry'] = ['prohibited'];
            $rules['card_cvv']    = ['prohibited'];
        }

        if ($method === 'paypal') {
            // $rules['paypal_token'] = ['required', 'string'];
            $rules['card_number'] = ['prohibited'];
            $rules['card_expiry'] = ['prohibited'];
            $rules['card_cvv']    = ['prohibited'];
        }

        return $rules;
    }



    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'payment_method.required' => 'Payment method is required',
            'payment_method.in' => 'Invalid payment method selected',
            'card_number.required_if' => 'Card number is required for credit card payments',
            'card_expiry.required_if' => 'Card expiry is required for credit card payments',
            'card_cvv.required_if' => 'CVV is required for credit card payments',
        ];
    }
}