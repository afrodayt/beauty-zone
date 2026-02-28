<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DeviceStoreRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'purchased_at' => ['nullable', 'date'],
            'cost' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'note' => ['nullable', 'string'],
        ];
    }
}
