<?php

namespace App\Http\Requests\Admin;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentStoreRequest extends FormRequest
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
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'visit_id' => ['nullable', 'integer', 'exists:visits,id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'payment_method' => ['required', Rule::in(PaymentMethod::values())],
            'paid_at' => ['required', 'date'],
            'note' => ['nullable', 'string'],
        ];
    }
}
