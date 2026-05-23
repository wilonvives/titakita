<?php

declare(strict_types=1);

namespace TitaKita\Http\Request\Event;

use TitaKita\Http\Request\BaseRequest;
use TitaKita\Validators\EventRules;

class UpdateEventRequest extends BaseRequest
{
    use EventRules;

    public function rules(): array
    {
        $rules =  $this->eventRules();
        unset($rules['organizer_id']);

        return $rules;
    }

    public function messages(): array
    {
        return $this->eventMessages();
    }
}
