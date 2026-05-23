<?php

namespace TitaKita\Services\Application\Handlers\Event\DTO;

use TitaKita\DataTransferObjects\AddressDTO;
use TitaKita\DataTransferObjects\Attributes\CollectionOf;
use TitaKita\DataTransferObjects\AttributesDTO;
use TitaKita\DataTransferObjects\BaseDTO;
use TitaKita\DomainObjects\Enums\EventCategory;
use TitaKita\DomainObjects\Status\EventStatus;
use Illuminate\Support\Collection;

class UpdateEventDTO extends BaseDTO
{
    public function __construct(
        public readonly string         $title,
        public readonly ?EventCategory $category,
        public readonly int            $account_id,
        public readonly int            $id,
        public readonly ?string        $start_date = null,
        public readonly ?string        $end_date = null,
        public readonly ?string        $description = null,
        #[CollectionOf(AttributesDTO::class)]
        public readonly ?Collection    $attributes = null,
        public readonly ?string        $timezone = null,
        public readonly ?string        $currency = null,
        public readonly ?string        $location = null,
        public readonly ?AddressDTO    $location_details = null,
        public readonly ?string        $status = EventStatus::DRAFT->name,
    )
    {
    }
}
