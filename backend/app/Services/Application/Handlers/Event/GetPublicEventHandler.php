<?php

namespace TitaKita\Services\Application\Handlers\Event;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\EventSettingDomainObject;
use TitaKita\DomainObjects\Generated\PromoCodeDomainObjectAbstract;
use TitaKita\DomainObjects\ImageDomainObject;
use TitaKita\DomainObjects\OrganizerDomainObject;
use TitaKita\DomainObjects\OrganizerSettingDomainObject;
use TitaKita\DomainObjects\ProductCategoryDomainObject;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\DomainObjects\ProductPriceDomainObject;
use TitaKita\DomainObjects\TaxAndFeesDomainObject;
use TitaKita\Repository\Eloquent\Value\OrderAndDirection;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\PromoCodeRepositoryInterface;
use TitaKita\Services\Application\Handlers\Event\DTO\GetPublicEventDTO;
use TitaKita\Services\Domain\Event\EventPageViewIncrementService;
use TitaKita\Services\Domain\Product\ProductFilterService;

class GetPublicEventHandler
{
    public function __construct(
        private readonly EventRepositoryInterface      $eventRepository,
        private readonly PromoCodeRepositoryInterface  $promoCodeRepository,
        private readonly ProductFilterService          $productFilterService,
        private readonly EventPageViewIncrementService $eventPageViewIncrementService,
    )
    {
    }

    public function handle(GetPublicEventDTO $data): EventDomainObject
    {
        $event = $this->eventRepository
            ->loadRelation(
                new Relationship(ProductCategoryDomainObject::class, [
                    new Relationship(ProductDomainObject::class,
                        nested: [
                            new Relationship(ProductPriceDomainObject::class),
                            new Relationship(TaxAndFeesDomainObject::class),
                        ],
                        orderAndDirections: [
                            new OrderAndDirection('order', 'asc'),
                        ]
                    ),
                ])
            )
            ->loadRelation(new Relationship(EventSettingDomainObject::class))
            ->loadRelation(new Relationship(ImageDomainObject::class))
            ->loadRelation(new Relationship(OrganizerDomainObject::class, nested: [
                new Relationship(ImageDomainObject::class),
                new Relationship(OrganizerSettingDomainObject::class),
            ], name: 'organizer'))
            ->findById($data->eventId);

        $promoCodeDomainObject = $this->promoCodeRepository->findFirstWhere([
            PromoCodeDomainObjectAbstract::EVENT_ID => $data->eventId,
            PromoCodeDomainObjectAbstract::CODE => $data->promoCode,
        ]);

        if (!$promoCodeDomainObject?->isValid()) {
            $promoCodeDomainObject = null;
        }

        if (!$data->isAuthenticated) {
            $this->eventPageViewIncrementService->increment($data->eventId, $data->ipAddress);
        }

        return $event->setProductCategories($this->productFilterService->filter(
            productsCategories: $event->getProductCategories(),
            promoCode: $promoCodeDomainObject
        ));
    }
}
