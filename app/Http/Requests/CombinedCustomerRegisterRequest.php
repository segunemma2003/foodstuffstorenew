<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CombinedCustomerRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contact_person_email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',

            // Customer member fields
            'company_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'company_email' => 'required|string|email|max:255',
            'address' => 'required|string',
            'contact_person_name' => 'required|string|max:255',
            'contact_person_phone' => 'required|string|max:20',

        ];
    }
}
