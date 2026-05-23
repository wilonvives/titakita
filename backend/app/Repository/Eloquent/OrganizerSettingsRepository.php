<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\OrganizerSettingDomainObject;
use TitaKita\Models\OrganizerSetting;
use TitaKita\Repository\Interfaces\OrganizerSettingsRepositoryInterface;

/**
 * @extends BaseRepository<OrganizerSettingDomainObject>
 */
class OrganizerSettingsRepository extends BaseRepository implements OrganizerSettingsRepositoryInterface
{
    protected function getModel(): string
    {
        return OrganizerSetting::class;
    }

    public function getDomainObject(): string
    {
        return OrganizerSettingDomainObject::class;
    }
}
