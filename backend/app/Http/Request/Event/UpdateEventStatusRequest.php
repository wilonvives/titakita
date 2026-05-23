<?php

namespace TitaKita\Http\Request\Event;

use TitaKita\DomainObjects\Status\EventStatus;
use TitaKita\Http\Request\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateEventStatusRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(EventStatus::valuesArray())],
        ];
    }
}
