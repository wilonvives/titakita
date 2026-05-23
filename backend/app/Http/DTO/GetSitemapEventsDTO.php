<?php

namespace TitaKita\Http\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;

class GetSitemapEventsDTO extends BaseDataObject
{
    public function __construct(
        public int $page,
    )
    {
    }
}
