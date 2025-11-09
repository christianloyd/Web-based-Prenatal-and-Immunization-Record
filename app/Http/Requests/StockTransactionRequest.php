<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['midwife', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'vaccine_id' => ['required', 'exists:vaccines,id'],
            'transaction_type' => ['required', 'in:in,out'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:255']
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'vaccine_id.required' => 'Please select a vaccine.',
            'vaccine_id.exists' => 'Selected vaccine does not exist.',
            'transaction_type.required' => 'Please select transaction type.',
            'transaction_type.in' => 'Invalid transaction type.',
            'quantity.required' => 'Quantity is required.',
            'quantity.min' => 'Quantity must be at least 1.',
            'reason.required' => 'Reason is required.',
            'reason.max' => 'Reason is too long.'
        ];
    }
}
