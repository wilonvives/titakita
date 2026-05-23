<?php

namespace TitaKita\Services\Application\Handlers\TicketLookup;

use Carbon\Carbon;
use TitaKita\DomainObjects\AttendeeDomainObject;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\EventSettingDomainObject;
use TitaKita\DomainObjects\Generated\EventDomainObjectAbstract;
use TitaKita\DomainObjects\Generated\OrderDomainObjectAbstract;
use TitaKita\DomainObjects\Generated\OrganizerDomainObjectAbstract;
use TitaKita\DomainObjects\Generated\ProductDomainObjectAbstract;
use TitaKita\DomainObjects\ImageDomainObject;
use TitaKita\DomainObjects\OrderDomainObject;
use TitaKita\DomainObjects\OrganizerDomainObject;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\DomainObjects\ProductPriceDomainObject;
use TitaKita\DomainObjects\Status\OrderStatus;
use TitaKita\DomainObjects\TicketLookupTokenDomainObject;
use TitaKita\Exceptions\InvalidTicketLookupTokenException;
use TitaKita\Repository\Eloquent\Value\OrderAndDirection;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\OrderRepositoryInterface;
use TitaKita\Repository\Interfaces\TicketLookupTokenRepositoryInterface;
use TitaKita\Services\Application\Handlers\TicketLookup\DTO\GetOrdersByLookupTokenDTO;
use Illuminate\Support\Collection;

class GetOrdersByLookupTokenHandler
{
    public function __construct(
        private readonly TicketLookupTokenRepositoryInterface $ticketLookupTokenRepository,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
    }

    /**
     * @throws InvalidTicketLookupTokenException
     * @return Collection<OrderDomainObject>
     */
    public function handle(GetOrdersByLookupTokenDTO $dto): Collection
    {
        $tokenRecord = $this->validateAndFetchToken($dto->token);

        return $this->getOrdersForEmail($tokenRecord->getEmail());
    }

    /**
     * @throws InvalidTicketLookupTokenException
     */
    private function validateAndFetchToken(string $token): TicketLookupTokenDomainObject
    {
        $tokenRecord = $this->ticketLookupTokenRepository->findFirstWhere(['token' => $token]);

        if (!$tokenRecord) {
            throw new InvalidTicketLookupTokenException(__('Invalid or expired link. Please request a new one.'));
        }

        if ($this->isTokenExpired($tokenRecord->getExpiresAt())) {
            throw new InvalidTicketLookupTokenException(__('This link has expired. Please request a new one.'));
        }

        return $tokenRecord;
    }

    private function isTokenExpired(string $expiresAt): bool
    {
        return (new Carbon($expiresAt))->isPast();
    }

    /**
     * @return Collection<OrderDomainObject>
     */
    private function getOrdersForEmail(string $email): Collection
    {
        return $this->orderRepository
            ->loadRelation(new Relationship(
                domainObject: AttendeeDomainObject::class,
                nested: [
                    new Relationship(
                        domainObject: ProductDomainObject::class,
                        nested: [
                            new Relationship(
                                domainObject: ProductPriceDomainObject::class,
                            )
                        ],
                        name: ProductDomainObjectAbstract::SINGULAR_NAME,
                    )
                ],
            ))
            ->loadRelation(new Relationship(
                domainObject: EventDomainObject::class,
                nested: [
                    new Relationship(
                        domainObject: EventSettingDomainObject::class,
                    ),
                    new Relationship(
                        domainObject: OrganizerDomainObject::class,
                        name: OrganizerDomainObjectAbstract::SINGULAR_NAME,
                    ),
                    new Relationship(
                        domainObject: ImageDomainObject::class,
                    )
                ],
                name: EventDomainObjectAbstract::SINGULAR_NAME
            ))
            ->findWhere(
                [
                    [OrderDomainObjectAbstract::EMAIL, '=', $email],
                    [OrderDomainObjectAbstract::STATUS, '=', OrderStatus::COMPLETED->name],
                ],
                orderAndDirections: [
                    new OrderAndDirection(OrderDomainObjectAbstract::CREATED_AT, 'desc'),
                ],
            );
    }
}
