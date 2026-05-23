<?php

namespace TitaKita\Events;

use TitaKita\DomainObjects\EventDomainObject;
use Illuminate\Foundation\Events\Dispatchable;

class EventUpdateEvent
{
    use Dispatchable;

    public function __construct(
        private readonly EventDomainObject $event,
    )
    {
    }
}
