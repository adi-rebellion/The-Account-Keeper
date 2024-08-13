<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionCreateRequest extends ApiRequestValidator
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'trans_amount' => 'required|numeric',
            'trans_type' => 'required|in:credit,debit',
            'category_id' => 'nullable' // Assuming you have a categories table
        ];
    }

    public function messages()
    {
        return [
            'trans_amount.required' => 'The transaction amount is required.',
            'trans_amount.numeric' => 'The transaction amount must be a number.',
            'trans_type.required' => 'The transaction type is required.',
            'trans_type.in' => 'The transaction type must be either credit or debit.',
        ];
    }
}
