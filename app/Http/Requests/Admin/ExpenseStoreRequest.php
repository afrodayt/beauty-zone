<?php

namespace App\Http\Requests\Admin;

use App\Enums\ExpenseType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(ExpenseType::values())],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
        ];
    }
}
