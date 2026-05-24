<?php

namespace TitaKita\Resources\Booking;

use Carbon\Carbon;
use Illuminate\Http\Request;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\Resources\BaseResource;
use TitaKita\Resources\Image\ImageResource;

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
        $capacity = $price?->getInitialQuantityAvailable();
        $capacityRemaining = $capacity === null
            ? null
            : max(0, $capacity - ($price?->getQuantitySold() ?? 0));

        $image = $product->getImages()?->first();

        return [
            'product_id' => $product->getId(),
            'session_start_at' => $product->getSessionStartAt(),
            'session_end_at' => $product->getSessionEndAt(),
            'duration_minutes' => $this->durationMinutes($product),
            'capacity' => $capacity,
            'capacity_remaining' => $capacityRemaining,
            'sold_out' => $capacityRemaining !== null && $capacityRemaining <= 0,
            'description' => $product->getDescription(),
            'image' => $image ? new ImageResource($image) : null,
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
