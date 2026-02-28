<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class ServiceUpdateRequest extends ServiceStoreRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $serviceId = $this->route('service')?->id;

        $rules['code'] = [
            'required',
            'string',
            'max:50',
            Rule::unique('services', 'code')->ignore($serviceId),
        ];

        return $rules;
    }
}
