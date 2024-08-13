<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends ApiRequestValidator
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
            'name' => 'required|string|max:50',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ];
    }

    // public function messages()unique:table,column,except,id
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
