<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Only midwives can create users.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->isMidwife();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'age' => 'nullable|integer|min:0|max:120',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:midwife,bhw',
            'gender' => 'required|in:Male,Female',
            'contact_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'name.max' => 'Name must not exceed 255 characters.',

            'username.required' => 'Username is required.',
            'username.max' => 'Username must not exceed 255 characters.',
            'username.unique' => 'Username is already taken. Please choose another.',

            'email.email' => 'Please provide a valid email address.',
            'email.max' => 'Email must not exceed 255 characters.',
            'email.unique' => 'Email is already registered in the system.',

            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters long.',

            'role.required' => 'Role is required.',
            'role.in' => 'Role must be either midwife or bhw.',

            'gender.required' => 'Gender is required.',
            'gender.in' => 'Gender must be either Male or Female.',

            'contact_number.max' => 'Contact number must not exceed 20 characters.',
            'address.max' => 'Address must not exceed 500 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'full name',
            'username' => 'username',
            'email' => 'email address',
            'password' => 'password',
            'role' => 'user role',
            'gender' => 'gender',
            'contact_number' => 'contact number',
            'address' => 'address',
        ];
    }

    /**
     * Prepare the data for validation.
     * Trim whitespace from string inputs.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->name ? trim($this->name) : null,
            'username' => $this->username ? trim($this->username) : null,
            'email' => $this->email ? trim($this->email) : null,
            'address' => $this->address ? trim($this->address) : null,
        ]);
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization()
    {
        throw new \Illuminate\Auth\Access\AuthorizationException(
            'Only midwives can create new users.'
        );
    }
}
