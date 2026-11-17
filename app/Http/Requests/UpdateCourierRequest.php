<?php

namespace App\Http\Requests;

use App\Models\Courier;

class UpdateCourierRequest extends BaseCourierRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Courier|null $courier */
        $courier = $this->route('courier');

        return $this->courierRules(
            courierId: $courier?->id,
            isUpdate: true,
        );
    }
}
