<?php

namespace TitaKita\Http\Request\Organizer;

use TitaKita\DomainObjects\Status\OrganizerStatus;
use TitaKita\Http\Request\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateOrganizerStatusRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(OrganizerStatus::valuesArray())],
        ];
    }
}