<?php

namespace TitaKita\Http\Request\CapacityAssigment;

use TitaKita\DomainObjects\Status\CapacityAssignmentStatus;
use TitaKita\Http\Request\BaseRequest;
use TitaKita\Validators\Rules\RulesHelper;
use Illuminate\Validation\Rule;

class UpsertCapacityAssignmentRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => RulesHelper::REQUIRED_STRING,
            'capacity' => ['nullable', 'numeric', 'min:1'],
            'status' => ['required', Rule::in(CapacityAssignmentStatus::valuesArray())],
            'product_ids' => ['required', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_ids.required' => __('Please select at least one product.'),
        ];
    }
}
