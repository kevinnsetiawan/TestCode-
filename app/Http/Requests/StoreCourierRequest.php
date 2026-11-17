<?php

namespace App\Http\Requests;

class StoreCourierRequest extends BaseCourierRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return $this->courierRules();
    }
}
