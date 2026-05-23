<?php

namespace TitaKita\Services\Application\Handlers\Waitlist\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;

class CreateWaitlistEntryDTO extends BaseDataObject
{
    public function __construct(
        public int     $event_id,
        public int     $product_price_id,
        public string  $email,
        public string  $first_name,
        public ?string $last_name = null,
        public string  $locale = 'en',
    )
    {
    }
}
