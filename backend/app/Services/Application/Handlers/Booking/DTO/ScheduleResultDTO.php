<?php

declare(strict_types=1);

namespace TitaKita\Services\Application\Handlers\Booking\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;
use TitaKita\DomainObjects\ScheduleDomainObject;

class ScheduleResultDTO extends BaseDataObject
{
    public function __construct(
        public ScheduleDomainObject $schedule,
        public int $generatedSessionCount,
    ) {}
}
