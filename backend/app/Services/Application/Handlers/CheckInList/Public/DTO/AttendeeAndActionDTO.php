<?php

namespace TitaKita\Services\Application\Handlers\CheckInList\Public\DTO;

use TitaKita\DomainObjects\Enums\AttendeeCheckInActionType;
use Spatie\LaravelData\Data;

class AttendeeAndActionDTO extends Data
{
    public function __construct(
        public string                    $public_id,
        public AttendeeCheckInActionType $action,
    )
    {
    }
}
