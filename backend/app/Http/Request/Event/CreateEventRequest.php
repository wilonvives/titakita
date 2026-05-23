<?php

declare(strict_types=1);

namespace TitaKita\Http\Request\Event;

use TitaKita\Http\Request\BaseRequest;
use TitaKita\Validators\EventRules;

class CreateEventRequest extends BaseRequest
{
    use EventRules;

    public function rules(): array
    {
        return $this->eventRules();
    }

    public function messages(): array
    {
        return $this->eventMessages();
    }
}
