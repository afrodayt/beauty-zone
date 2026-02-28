<?php

namespace App\Http\Requests\Admin;

use App\Enums\ClientStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientStoreRequest extends FormRequest
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
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'birth_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(ClientStatus::values())],
            'notes' => ['nullable', 'string'],
            'contra_pregnancy' => ['nullable', 'boolean'],
            'contra_allergy' => ['nullable', 'boolean'],
            'contra_skin_damage' => ['nullable', 'boolean'],
            'contra_varicose' => ['nullable', 'boolean'],
        ];
    }
}
