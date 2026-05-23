<?php

namespace TitaKita\Services\Application\Handlers\Question;

use TitaKita\Repository\Interfaces\QuestionAndAnswerViewRepositoryInterface;
use Illuminate\Support\Collection;

class ExportAnswersHandler
{
    public function __construct(
        private readonly QuestionAndAnswerViewRepositoryInterface $questionAndAnswerViewRepository,
    )
    {
    }

    public function handle(int $eventId): Collection
    {
        return $this->questionAndAnswerViewRepository->findWhere([
            'event_id' => $eventId,
        ]);
    }
}
