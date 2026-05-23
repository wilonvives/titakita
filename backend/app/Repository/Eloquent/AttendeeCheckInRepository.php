<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\AttendeeCheckInDomainObject;
use TitaKita\Models\AttendeeCheckIn;
use TitaKita\Repository\Interfaces\AttendeeCheckInRepositoryInterface;

/**
 * @extends BaseRepository<AttendeeCheckInDomainObject>
 */
class AttendeeCheckInRepository extends BaseRepository implements AttendeeCheckInRepositoryInterface
{
    protected function getModel(): string
    {
        return AttendeeCheckIn::class;
    }

    public function getDomainObject(): string
    {
        return AttendeeCheckInDomainObject::class;
    }
}
