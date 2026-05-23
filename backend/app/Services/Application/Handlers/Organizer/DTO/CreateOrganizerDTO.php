<?php

namespace TitaKita\Services\Application\Handlers\Organizer\DTO;

use TitaKita\DataTransferObjects\BaseDTO;
use Illuminate\Http\UploadedFile;

class CreateOrganizerDTO extends BaseDTO
{
    public function __construct(
        public string        $name,
        public string        $email,
        public int           $account_id,
        public string        $timezone,
        public string        $currency,
        public ?string       $phone = null,
        public ?string       $website = null,
        public ?string       $description = null,
        public ?UploadedFile $logo = null,
    )
    {
    }
}
