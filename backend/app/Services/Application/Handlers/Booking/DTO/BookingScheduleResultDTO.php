<?php

declare(strict_types=1);

namespace TitaKita\Services\Application\Handlers\Booking\DTO;

use Illuminate\Support\Collection;
use TitaKita\DataTransferObjects\BaseDataObject;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\DomainObjects\ScheduleDomainObject;

class BookingScheduleResultDTO extends BaseDataObject
{
    /**
     * @param  Collection<ProductDomainObject>  $sessions
     */
    public function __construct(
        public int $eventId,
        public ?ScheduleDomainObject $schedule,
        public Collection $sessions,
    ) {}
}
