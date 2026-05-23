<?php

namespace TitaKita\Services\Application\Handlers\Attendee;

use TitaKita\DomainObjects\AttendeeCheckInDomainObject;
use TitaKita\DomainObjects\OrderDomainObject;
use TitaKita\Http\DTO\QueryParamsDTO;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\AttendeeRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class GetAttendeesHandler
{
    public function __construct(
        private readonly AttendeeRepositoryInterface $attendeeRepository,
    )
    {
    }

    public function handle(int $eventId, QueryParamsDTO $queryParams): LengthAwarePaginator
    {
        return $this->attendeeRepository
            ->loadRelation(new Relationship(
                domainObject: OrderDomainObject::class,
                name: 'order'
            ))
            ->loadRelation(new Relationship(
                domainObject: AttendeeCheckInDomainObject::class,
                name: 'check_ins'
            ))
            ->findByEventId($eventId, $queryParams);
    }
}
