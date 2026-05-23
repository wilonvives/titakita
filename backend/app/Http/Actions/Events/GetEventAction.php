<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Events;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\ImageDomainObject;
use TitaKita\DomainObjects\OrganizerDomainObject;
use TitaKita\DomainObjects\ProductCategoryDomainObject;
use TitaKita\DomainObjects\TaxAndFeesDomainObject;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\DomainObjects\ProductPriceDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Resources\Event\EventResource;
use Illuminate\Http\JsonResponse;

class GetEventAction extends BaseAction
{
    private EventRepositoryInterface $eventRepository;

    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function __invoke(int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $event = $this->eventRepository
            ->loadRelation(new Relationship(domainObject: OrganizerDomainObject::class, name: 'organizer'))
            ->loadRelation(new Relationship(ImageDomainObject::class))
            ->loadRelation(
                new Relationship(ProductCategoryDomainObject::class, [
                    new Relationship(ProductDomainObject::class, [
                        new Relationship(ProductPriceDomainObject::class),
                        new Relationship(TaxAndFeesDomainObject::class),
                    ]),
                ])
            )
            ->findById($eventId);

        return $this->resourceResponse(EventResource::class, $event);
    }
}
