<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\EventDailyStatisticDomainObject;
use TitaKita\Models\EventDailyStatistic;
use TitaKita\Repository\Interfaces\EventDailyStatisticRepositoryInterface;

/**
 * @extends BaseRepository<EventDailyStatisticDomainObject>
 */
class EventDailyStatisticRepository extends BaseRepository implements EventDailyStatisticRepositoryInterface
{
    protected function getModel(): string
    {
        return EventDailyStatistic::class;
    }

    public function getDomainObject(): string
    {
        return EventDailyStatisticDomainObject::class;
    }
}
