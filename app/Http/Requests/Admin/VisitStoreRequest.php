<?php

namespace App\Http\Requests\Admin;

use App\Enums\PaymentMethod;
use App\Enums\VisitStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VisitStoreRequest extends FormRequest
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
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'device_id' => ['nullable', 'integer', 'exists:devices,id'],
            'master_id' => ['nullable', 'integer', 'exists:users,id'],
            'client_package_id' => ['nullable', 'integer', 'exists:client_packages,id'],
            'zone' => ['required', 'string', 'max:100'],
            'starts_at' => ['required', 'date'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'payment_method' => ['required', Rule::in(PaymentMethod::values())],
            'visit_status' => ['required', Rule::in(VisitStatus::values())],
            'master_comment' => ['nullable', 'string'],
            'deduct_from_package' => ['nullable', 'boolean'],
        ];
    }
}
