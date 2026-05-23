<?php

namespace TitaKita\Services\Application\Handlers\Admin\DTO;

use TitaKita\DataTransferObjects\BaseDataObject;

class GetAllMessagesForAdminDTO extends BaseDataObject
{
    public function __construct(
        public readonly int $perPage = 20,
        public readonly ?string $search = null,
        public readonly ?string $status = null,
        public readonly ?string $type = null,
        public readonly ?string $sortBy = 'created_at',
        public readonly ?string $sortDirection = 'desc',
    ) {
    }
}
