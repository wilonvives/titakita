<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\EventSettingDomainObject;
use TitaKita\Models\EventSetting;
use TitaKita\Repository\Interfaces\EventSettingsRepositoryInterface;

/**
 * @extends BaseRepository<EventSettingDomainObject>
 */
class EventSettingsRepository extends BaseRepository implements EventSettingsRepositoryInterface
{
    protected function getModel(): string
    {
        return EventSetting::class;
    }

    public function getDomainObject(): string
    {
        return EventSettingDomainObject::class;
    }
}
