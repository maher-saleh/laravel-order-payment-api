<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for creating orders.
 * 
 * Centralizes validation logic, keeping controllers clean.
 */
class StoreOrderRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; //Authorization handled by middleware
    }



    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'items.*.price' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
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
            'items.required' => 'At least one item is required',
            'items.*.product_name.required' => 'Product name is required for all items',
            'items.*.quantity.min' => 'Quantity must be at least 1',
            'items.*.price.min' => 'Price must be greater than 0',
        ];
    }



    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'items.*.product_name' => 'product name',
            'items.*.quantity' => 'quantity',
            'items.*.price' => 'price',
        ];
    }
}