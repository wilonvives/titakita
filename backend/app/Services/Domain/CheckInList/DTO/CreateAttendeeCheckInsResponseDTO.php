<?php

namespace TitaKita\Services\Domain\CheckInList\DTO;

use TitaKita\DataTransferObjects\BaseDTO;
use TitaKita\DataTransferObjects\ErrorBagDTO;
use Illuminate\Support\Collection;

class CreateAttendeeCheckInsResponseDTO extends BaseDTO
{
    public function __construct(
        public Collection  $attendeeCheckIns,
        public ErrorBagDTO $errors,
    )
    {
    }
}
