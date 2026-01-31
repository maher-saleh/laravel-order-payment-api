<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Order;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // User can only update their own orders
        return $this->user()->id === $this->route('order')->user_id;
    }



    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'sometimes',
                'required',
                Rule::in([Order::STATUS_PENDING, Order::STATUS_CONFIRMED, Order::STATUS_CANCELLED])
            ],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.product_name' => ['required_with:items', 'string', 'max:255'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1', 'max:1000'],
            'items.*.price' => ['required_with:items', 'numeric', 'min:0.01', 'max:999999.99'],
        ];
    }

    

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.in' => 'Invalid order status',
            'items.min' => 'At least one item is required when updating items',
        ];
    }
}