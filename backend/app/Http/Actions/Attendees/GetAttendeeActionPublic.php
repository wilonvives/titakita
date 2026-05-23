<?php

namespace TitaKita\Http\Actions\Attendees;

use TitaKita\DomainObjects\Generated\AttendeeDomainObjectAbstract;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\DomainObjects\ProductPriceDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\AttendeeRepositoryInterface;
use TitaKita\Resources\Attendee\AttendeeResourcePublic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class GetAttendeeActionPublic extends BaseAction
{
    private AttendeeRepositoryInterface $attendeeRepository;

    public function __construct(AttendeeRepositoryInterface $attendeeRepository)
    {
        $this->attendeeRepository = $attendeeRepository;
    }

    /**
     * @todo move to handler
     */
    public function __invoke(int $eventId, string $attendeeShortId): JsonResponse|Response
    {
        $attendee = $this->attendeeRepository
            ->loadRelation(new Relationship(
                domainObject: ProductDomainObject::class,
                nested: [
                    new Relationship(
                        domainObject: ProductPriceDomainObject::class,
                    ),
                ], name: 'product'))
            ->findFirstWhere([
                AttendeeDomainObjectAbstract::SHORT_ID => $attendeeShortId
            ]);

        if (!$attendee) {
            return $this->notFoundResponse();
        }

        return $this->resourceResponse(AttendeeResourcePublic::class, $attendee);
    }
}
