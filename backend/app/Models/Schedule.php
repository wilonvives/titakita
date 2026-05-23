<?php

declare(strict_types=1);

namespace TitaKita\Models;

use TitaKita\DomainObjects\Generated\ScheduleDomainObjectAbstract;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends BaseModel
{
    protected function getCastMap(): array
    {
        return [
            ScheduleDomainObjectAbstract::START_TIMES => 'array',
            ScheduleDomainObjectAbstract::WEEKDAYS => 'array',
            ScheduleDomainObjectAbstract::SPECIFIC_DATES => 'array',
            ScheduleDomainObjectAbstract::RANGE_START_DATE => 'date',
            ScheduleDomainObjectAbstract::RANGE_END_DATE => 'date',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
