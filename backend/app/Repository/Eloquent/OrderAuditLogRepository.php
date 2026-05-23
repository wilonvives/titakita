<?php

declare(strict_types=1);

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\OrderAuditLogDomainObject;
use TitaKita\Models\OrderAuditLog;
use TitaKita\Repository\Interfaces\OrderAuditLogRepositoryInterface;

/**
 * @extends BaseRepository<OrderAuditLogDomainObject>
 */
class OrderAuditLogRepository extends BaseRepository implements OrderAuditLogRepositoryInterface
{
    protected function getModel(): string
    {
        return OrderAuditLog::class;
    }

    public function getDomainObject(): string
    {
        return OrderAuditLogDomainObject::class;
    }
}
