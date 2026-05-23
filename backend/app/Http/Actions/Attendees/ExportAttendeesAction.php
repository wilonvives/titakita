<?php

namespace TitaKita\Http\Actions\Attendees;

use TitaKita\DomainObjects\AttendeeCheckInDomainObject;
use TitaKita\DomainObjects\CheckInListDomainObject;
use TitaKita\DomainObjects\Enums\QuestionBelongsTo;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\OrderDomainObject;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\DomainObjects\ProductPriceDomainObject;
use TitaKita\DomainObjects\QuestionAndAnswerViewDomainObject;
use TitaKita\Exports\AttendeesExport;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\AttendeeRepositoryInterface;
use TitaKita\Repository\Interfaces\QuestionRepositoryInterface;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportAttendeesAction extends BaseAction
{
    public function __construct(
        private readonly AttendeesExport             $export,
        private readonly AttendeeRepositoryInterface $attendeeRepository,
        private readonly QuestionRepositoryInterface $questionRepository
    )
    {
    }

    /**
     * @todo This should be passed off to a queue and moved to a service
     */
    public function __invoke(int $eventId): BinaryFileResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $attendees = $this->attendeeRepository
            ->loadRelation(QuestionAndAnswerViewDomainObject::class)
            ->loadRelation(new Relationship(
                domainObject: AttendeeCheckInDomainObject::class,
                nested: [
                    new Relationship(
                        domainObject: CheckInListDomainObject::class,
                        name: 'check_in_list',
                    ),
                ],
                name: 'check_ins',
            ))
            ->loadRelation(new Relationship(
                domainObject: ProductDomainObject::class,
                nested: [
                    new Relationship(
                        domainObject: ProductPriceDomainObject::class,
                    ),
                ],
                name: 'product'
            ))
            ->loadRelation(new Relationship(
                domainObject: OrderDomainObject::class,
                nested: [
                    new Relationship(
                        domainObject: QuestionAndAnswerViewDomainObject::class
                    )
                ],
                name: 'order'
            ))
            ->findByEventIdForExport($eventId);

        $productQuestions = $this->questionRepository->findWhere([
            'event_id' => $eventId,
            'belongs_to' => QuestionBelongsTo::PRODUCT->name,
        ]);

        $orderQuestions = $this->questionRepository->findWhere([
            'event_id' => $eventId,
            'belongs_to' => QuestionBelongsTo::ORDER->name,
        ]);

        return Excel::download(
            $this->export->withData($attendees, $productQuestions, $orderQuestions),
            'attendees.xlsx'
        );
    }
}
