<?php

declare(strict_types=1);

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\ScheduleDomainObject;
use TitaKita\Models\Schedule;
use TitaKita\Repository\Interfaces\ScheduleRepositoryInterface;

/**
 * @extends BaseRepository<ScheduleDomainObject>
 */
class ScheduleRepository extends BaseRepository implements ScheduleRepositoryInterface
{
    protected function getModel(): string
    {
        return Schedule::class;
    }

    public function getDomainObject(): string
    {
        return ScheduleDomainObject::class;
    }
}
