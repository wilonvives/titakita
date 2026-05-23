<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\EventStatisticDomainObject;
use TitaKita\Models\EventStatistic;
use TitaKita\Repository\Interfaces\EventStatisticRepositoryInterface;

/**
 * @extends BaseRepository<EventStatisticDomainObject>
 */
class EventStatisticRepository extends BaseRepository implements EventStatisticRepositoryInterface
{
    protected function getModel(): string
    {
        return EventStatistic::class;
    }

    public function getDomainObject(): string
    {
        return EventStatisticDomainObject::class;
    }
}
