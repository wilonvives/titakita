<?php

namespace HiEvents\Services\Application\Handlers\TitaKita;

use HiEvents\DomainObjects\ImageDomainObject;
use HiEvents\DomainObjects\OrganizerDomainObject;
use HiEvents\DomainObjects\Status\EventStatus;
use HiEvents\Http\DTO\QueryParamsDTO;
use HiEvents\Repository\Eloquent\Value\Relationship;
use HiEvents\Repository\Interfaces\EventRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Node-wide public events feed for the TitaKita directory contract.
 * Returns only LIVE (publicly visible) events across all organizers.
 */
class GetPublicEventsFeedHandler
{
    public function __construct(private readonly EventRepositoryInterface $eventRepository)
    {
    }

    public function handle(QueryParamsDTO $params): LengthAwarePaginator
    {
        return $this->eventRepository
            ->loadRelation(new Relationship(OrganizerDomainObject::class))
            ->loadRelation(new Relationship(ImageDomainObject::class))
            ->findEvents(
                where: ['status' => EventStatus::LIVE->name],
                params: $params,
            );
    }
}
