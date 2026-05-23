<?php

namespace TitaKita\Services\Application\Handlers\Question;

use TitaKita\Services\Application\Handlers\Question\DTO\EditQuestionAnswerDTO;
use TitaKita\Services\Domain\Question\EditQuestionAnswerService;
use TitaKita\Services\Domain\Question\Exception\InvalidAnswerException;
use JsonException;

class EditQuestionAnswerHandler
{
    public function __construct(
        private readonly EditQuestionAnswerService $editQuestionAnswerService,
    )
    {
    }

    /**
     * @throws InvalidAnswerException
     * @throws JsonException
     */
    public function handle(EditQuestionAnswerDTO $editQuestionAnswerDTO): void
    {
        $this->editQuestionAnswerService->editQuestionAnswer(
            eventId: $editQuestionAnswerDTO->eventId,
            questionAnswerId: $editQuestionAnswerDTO->questionAnswerId,
            answer: $editQuestionAnswerDTO->answer,
        );
    }
}
