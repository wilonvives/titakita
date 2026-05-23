<?php

namespace TitaKita\Resources\Tax;

use TitaKita\DomainObjects\TaxAndFeesDomainObject;
use TitaKita\Resources\BaseResource;

/**
 * @mixin TaxAndFeesDomainObject
 */
class TaxAndFeeResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->getId(),
            'account_id' => $this->getAccountId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'calculation_type' => $this->getCalculationType(),
            'rate' => $this->getRate(),
            'is_active' => $this->getIsActive(),
            'is_default' => $this->getIsDefault(),
            'type' => $this->getType(),
        ];
    }
}
