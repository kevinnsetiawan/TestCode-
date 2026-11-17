<?php

namespace App\Http\Requests;

use App\Models\Courier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class BaseCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Build validation rules shared between store and update requests.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function courierRules(?int $courierId = null, bool $isUpdate = false): array
    {
        $presence = $isUpdate ? 'sometimes' : 'required';

        return [
            'code' => [
                $presence,
                'string',
                'max:32',
                Rule::unique('couriers', 'code')->ignore($courierId),
            ],
            'name' => [
                $presence,
                'string',
                'min:2',
                'max:255',
            ],
            'email' => [
                $presence,
                'string',
                'email:rfc',
                'max:255',
                Rule::unique('couriers', 'email')->ignore($courierId),
            ],
            'phone' => [
                $presence,
                'string',
                'min:8',
                'max:25',
                'regex:/^[0-9+\-\s()]+$/',
                Rule::unique('couriers', 'phone')->ignore($courierId),
            ],
            'level' => [
                $presence,
                'integer',
                'between:'.Courier::MIN_LEVEL.','.Courier::MAX_LEVEL,
            ],
            'vehicle_type' => [
                $presence,
                'string',
                Rule::in(Courier::VEHICLE_TYPES),
            ],
            'license_plate' => [
                'nullable',
                'string',
                'max:20',
            ],
            'service_area' => [
                'nullable',
                'string',
                'max:100',
            ],
            'status' => [
                'sometimes',
                'string',
                Rule::in(Courier::STATUSES),
            ],
            'registered_at' => [
                'nullable',
                'date',
            ],
        ];
    }
}
