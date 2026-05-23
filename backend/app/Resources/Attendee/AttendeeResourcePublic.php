<?php

namespace TitaKita\Resources\Attendee;

use TitaKita\DomainObjects\AttendeeDomainObject;
use TitaKita\Resources\Product\ProductMinimalResourcePublic;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AttendeeDomainObject
 */
class AttendeeResourcePublic extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getId(),
            'email' => $this->getEmail(),
            'status' => $this->getStatus(),
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'public_id' => $this->getPublicId(),
            'short_id' => $this->getShortId(),
            'product_id' => $this->getProductId(),
            'product_price_id' => $this->getProductPriceId(),
            'product' => $this->when((bool)$this->getProduct(), fn() => new ProductMinimalResourcePublic($this->getProduct())),
            'locale' => $this->getLocale(),
        ];
    }
}
