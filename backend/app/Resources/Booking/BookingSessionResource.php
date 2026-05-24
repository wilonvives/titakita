<?php

namespace TitaKita\Resources\Booking;

use Carbon\Carbon;
use Illuminate\Http\Request;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\Resources\BaseResource;

/**
 * @mixin ProductDomainObject
 */
class BookingSessionResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        /** @var ProductDomainObject $product */
        $product = $this->resource;

        $price = $product->getProductPrices()?->first();
        $capacityRemaining = $price?->getQuantityAvailable();

        return [
            'product_id' => $product->getId(),
            'session_start_at' => $product->getSessionStartAt(),
            'session_end_at' => $product->getSessionEndAt(),
            'duration_minutes' => $this->durationMinutes($product),
            'capacity' => $price?->getInitialQuantityAvailable(),
            'capacity_remaining' => $capacityRemaining,
            'sold_out' => $capacityRemaining !== null && $capacityRemaining <= 0,
            'description' => $product->getDescription(),
        ];
    }

    private function durationMinutes(ProductDomainObject $product): ?int
    {
        $start = $product->getSessionStartAt();
        $end = $product->getSessionEndAt();

        if ($start === null || $end === null) {
            return null;
        }

        return (int) Carbon::parse($start, 'UTC')->diffInMinutes(Carbon::parse($end, 'UTC'));
    }
}
