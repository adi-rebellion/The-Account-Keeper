<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends ApiRequestValidator
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ];
    }

    // public function messages()
    // {
    //     return [
    //         'name.required' => 'The name field is required.',
    //         'email.required' => 'The email field is required.',
    //         'email.email' => 'The email must be a valid email address.',
    //         'email.unique' => 'The email has already been taken.',
    //         'password.required' => 'The password field is required.',
    //         'password.confirmed' => 'The password confirmation does not match.',
    //     ];
    // }
}
