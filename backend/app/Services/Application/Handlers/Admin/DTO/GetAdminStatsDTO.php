<?php

namespace TitaKita\Services\Application\Handlers\Admin\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;

class GetAdminStatsDTO extends BaseDataObject
{
    public function __construct(
        public readonly int $total_users,
        public readonly int $total_accounts,
        public readonly int $total_live_events,
        public readonly int $total_tickets_sold,
    )
    {
    }
}
