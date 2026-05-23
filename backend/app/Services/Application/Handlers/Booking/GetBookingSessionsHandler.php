<?php

declare(strict_types=1);

namespace TitaKita\Services\Application\Handlers\Booking;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use TitaKita\Constants;
use TitaKita\DomainObjects\Generated\ProductDomainObjectAbstract;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\DomainObjects\ProductPriceDomainObject;
use TitaKita\Repository\Eloquent\Value\OrderAndDirection;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductRepositoryInterface;

class GetBookingSessionsHandler
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly EventRepositoryInterface $eventRepository,
    ) {}

    /**
     * Returns future, non-hidden, on-sale session-products grouped by local date.
     *
     * @return Collection<int, array{date: string, sessions: array<int, array<string, mixed>>}>
     */
    public function handle(int $eventId): Collection
    {
        $event = $this->eventRepository->findById($eventId);
        $timezone = $event->getTimezone() ?? config('app.default_timezone');

        $nowUtc = Carbon::now('UTC')->format('Y-m-d H:i:s');

        $products = $this->productRepository
            ->loadRelation(ProductPriceDomainObject::class)
            ->findWhere(
                [
                    [ProductDomainObjectAbstract::EVENT_ID, '=', $eventId],
                    [ProductDomainObjectAbstract::SCHEDULE_ID, 'not null', null],
                    [ProductDomainObjectAbstract::SESSION_START_AT, '>', $nowUtc],
                ],
                ['*'],
                [new OrderAndDirection(order: ProductDomainObjectAbstract::SESSION_START_AT, direction: 'asc')],
            );

        $grouped = [];

        foreach ($products as $product) {
            if (! $this->isOnSale($product)) {
                continue;
            }

            $price = $product->getProductPrices()?->first();

            if ($price === null) {
                continue;
            }

            $remaining = $this->productRepository->getQuantityRemainingForProductPrice(
                $product->getId(),
                $price->getId(),
            );

            $unlimited = $remaining >= Constants::INFINITE;
            $localStart = Carbon::parse($product->getSessionStartAt(), 'UTC')->setTimezone($timezone);
            $date = $localStart->format('Y-m-d');

            $grouped[$date] ??= [
                'date' => $date,
                'sessions' => [],
            ];

            $grouped[$date]['sessions'][] = [
                'product_id' => $product->getId(),
                'product_price_id' => $price->getId(),
                'session_start_at' => $product->getSessionStartAt(),
                'session_end_at' => $product->getSessionEndAt(),
                'capacity_remaining' => $unlimited ? null : max(0, $remaining),
                'is_sold_out' => ! $unlimited && $remaining <= 0,
            ];
        }

        return collect(array_values($grouped));
    }

    private function isOnSale(ProductDomainObject $product): bool
    {
        if ($product->getIsHidden()) {
            return false;
        }

        return ! $product->isBeforeSaleStartDate() && ! $product->isAfterSaleEndDate();
    }
}
