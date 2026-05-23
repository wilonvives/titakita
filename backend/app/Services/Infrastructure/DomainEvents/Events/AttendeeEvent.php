<?php

namespace TitaKita\Services\Infrastructure\DomainEvents\Events;

use TitaKita\Services\Infrastructure\DomainEvents\Enums\DomainEventType;

class AttendeeEvent extends BaseDomainEvent
{
    public function __construct(
        public DomainEventType $type,
        public int             $attendeeId,
    )
    {
    }
}
