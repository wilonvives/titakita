<?php

declare(strict_types=1);

namespace TitaKita\Services\Application\Handlers\Booking\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;

class SaveBookingSettingsDTO extends BaseDataObject
{
    public function __construct(
        public int $event_id,
        public int $session_duration_minutes,
        public ?int $capacity_per_session = null,
        public float $default_price = 0,
    ) {}
}
