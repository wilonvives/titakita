<?php

namespace TitaKita\Services\Application\Handlers\Event;

use TitaKita\DomainObjects\EventSettingDomainObject;
use TitaKita\DomainObjects\EventStatisticDomainObject;
use TitaKita\DomainObjects\ImageDomainObject;
use TitaKita\DomainObjects\OrganizerDomainObject;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\DomainObjects\ProductPriceDomainObject;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Services\Application\Handlers\Event\DTO\GetEventsDTO;
use Illuminate\Pagination\LengthAwarePaginator;

class GetEventsHandler
{
    public function __construct(
        private readonly EventRepositoryInterface $eventRepository,
    )
    {
    }

    public function handle(GetEventsDTO $dto): LengthAwarePaginator
    {
        return $this->eventRepository
            ->loadRelation(new Relationship(ImageDomainObject::class))
            ->loadRelation(new Relationship(EventSettingDomainObject::class))
            ->loadRelation(new Relationship(EventStatisticDomainObject::class))
            ->loadRelation(new Relationship(
                domainObject: ProductDomainObject::class,
                nested: [
                    new Relationship(ProductPriceDomainObject::class),
                ],
            ))
            ->loadRelation(new Relationship(
                domainObject: OrganizerDomainObject::class,
                name: 'organizer',
            ))
            ->findEvents(
                where: [
                    'account_id' => $dto->accountId,
                ],
                params: $dto->queryParams
            );
    }
}
