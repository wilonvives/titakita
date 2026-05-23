<?php

namespace TitaKita\Exports;

use TitaKita\DomainObjects\Enums\QuestionBelongsTo;
use TitaKita\DomainObjects\QuestionAndAnswerViewDomainObject;
use TitaKita\Exports\AnswerExportSheets\AttendeeAnswersSheet;
use TitaKita\Exports\AnswerExportSheets\OrderAnswersSheet;
use TitaKita\Exports\AnswerExportSheets\ProductAnswersSheet;
use TitaKita\Services\Domain\Question\QuestionAnswerFormatter;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AnswersExport implements WithMultipleSheets
{
    private Collection $answers;

    public function __construct(
        private readonly QuestionAnswerFormatter $questionAnswerFormatter,
    )
    {
    }

    public function withData(Collection $answers): AnswersExport
    {
        $this->answers = $answers;
        return $this;
    }

    public function sheets(): array
    {
        $attendeeAnswers = $this->answers->filter(function (QuestionAndAnswerViewDomainObject $answer) {
            return $answer->getBelongsTo() === QuestionBelongsTo::PRODUCT->name && $answer->getAttendeeId() !== null;
        })->sortBy([
            ['title', 'asc'],
            ['order_id', 'asc'],
            ['attendee_id', 'asc']
        ]);

        $productAnswers = $this->answers->filter(function (QuestionAndAnswerViewDomainObject $answer) {
            return $answer->getBelongsTo() === QuestionBelongsTo::PRODUCT->name && $answer->getAttendeeId() === null;
        })->sortBy([
            ['title', 'asc'],
            ['order_id', 'asc']
        ]);

        $orderAnswers = $this->answers->filter(function (QuestionAndAnswerViewDomainObject $answer) {
            return $answer->getBelongsTo() === QuestionBelongsTo::ORDER->name;
        })->sortBy([
            ['title', 'asc'],
            ['order_id', 'asc']
        ]);

        return [
            new AttendeeAnswersSheet($attendeeAnswers, $this->questionAnswerFormatter),
            new ProductAnswersSheet($productAnswers, $this->questionAnswerFormatter),
            new OrderAnswersSheet($orderAnswers, $this->questionAnswerFormatter),
        ];
    }
}
