<?php

namespace TitaKita\Services\Application\Handlers\Organizer\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;
use Illuminate\Http\UploadedFile;

class EditOrganizerDTO extends BaseDataObject
{
    public function __construct(
        public int             $id,
        public string          $name,
        public string          $email,
        public int             $account_id,
        public string          $timezone,
        public string          $currency,
        public ?string         $phone = null,
        public ?string         $website = null,
        public ?string         $description = null,
        public ?UploadedFile   $logo = null,
    )
    {
    }
}
