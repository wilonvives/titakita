<?php

namespace TitaKita\Services\Application\Handlers\Admin\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;

class GetAdminDashboardDataDTO extends BaseDataObject
{
    public function __construct(
        public readonly int $days = 14,
        public readonly int $limit = 10,
    ) {
    }
}
