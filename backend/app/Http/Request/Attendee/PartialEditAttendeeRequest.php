<?php

namespace TitaKita\Http\Request\Attendee;

use TitaKita\DomainObjects\Status\AttendeeStatus;
use TitaKita\Http\Request\BaseRequest;
use TitaKita\Validators\Rules\InsensitiveIn;

class PartialEditAttendeeRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'status' => ['sometimes', new InsensitiveIn(AttendeeStatus::valuesArray())],
            'first_name' => ['sometimes', 'string', 'max:100', 'min:1'],
            'last_name' => ['sometimes', 'string', 'max:100', 'min:1'],
            'email' => ['sometimes', 'email', 'max:100'],
        ];
    }
}
