<?php

namespace App\Http\Requests\Admin;

use App\Enums\PackageStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientPackageStoreRequest extends FormRequest
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
            'package_template_id' => ['nullable', 'integer', 'exists:package_templates,id'],
            'name' => ['required', 'string', 'max:255'],
            'total_procedures' => ['required', 'integer', 'min:1'],
            'remaining_procedures' => ['required', 'integer', 'min:0'],
            'purchased_amount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'expires_at' => ['nullable', 'date'],
            'status' => ['required', Rule::in(PackageStatus::values())],
        ];
    }
}
