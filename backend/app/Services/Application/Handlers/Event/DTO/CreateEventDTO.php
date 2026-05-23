<?php

namespace TitaKita\Services\Application\Handlers\Event\DTO;

use TitaKita\DataTransferObjects\AddressDTO;
use TitaKita\DataTransferObjects\Attributes\CollectionOf;
use TitaKita\DataTransferObjects\AttributesDTO;
use TitaKita\DataTransferObjects\BaseDTO;
use TitaKita\DomainObjects\Enums\EventCategory;
use TitaKita\DomainObjects\Status\EventStatus;
use TitaKita\Services\Application\Handlers\EventSettings\DTO\UpdateEventSettingsDTO;
use Illuminate\Support\Collection;

class CreateEventDTO extends BaseDTO
{
    public function __construct(
        public readonly string         $title,
        public readonly int            $organizer_id,
        public readonly int            $account_id,
        public readonly int            $user_id,
        public readonly ?int           $id = null,
        public readonly ?string        $start_date = null,
        public readonly ?string        $end_date = null,
        public readonly ?string        $description = null,
        #[CollectionOf(AttributesDTO::class)]
        public readonly ?Collection    $attributes = null,
        public readonly ?string        $timezone = null,
        public readonly ?string        $currency = null,
        public readonly ?EventCategory $category = null,
        public readonly ?AddressDTO    $location_details = null,
        public readonly ?string        $status = EventStatus::DRAFT->name,

        public ?UpdateEventSettingsDTO $event_settings = null
    )
    {
    }
}
