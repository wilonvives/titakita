<?php

declare(strict_types=1);

namespace TitaKita\Services\Application\Handlers\Booking\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;
use TitaKita\DomainObjects\Enums\ScheduleScopeType;

class UpsertScheduleDTO extends BaseDataObject
{
    public function __construct(
        public int $event_id,
        public int $session_duration_minutes,
        /** @var string[] */
        public array $start_times,
        public ScheduleScopeType $scope_type,
        public ?int $capacity_per_session = null,
        /** @var int[]|null */
        public ?array $weekdays = null,
        public ?string $range_start_date = null,
        public ?string $range_end_date = null,
        /** @var string[]|null */
        public ?array $specific_dates = null,
    ) {}
}
