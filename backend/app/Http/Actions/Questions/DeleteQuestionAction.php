<?php

namespace TitaKita\Http\Actions\Questions;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Exceptions\CannotDeleteEntityException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Application\Handlers\Question\DeleteQuestionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class DeleteQuestionAction extends BaseAction
{
    private DeleteQuestionHandler $deleteQuestionHandler;

    public function __construct(DeleteQuestionHandler $deleteQuestionHandler)
    {
        $this->deleteQuestionHandler = $deleteQuestionHandler;
    }

    /**
     * @throws Throwable
     * @throws CannotDeleteEntityException
     */
    public function __invoke(int $eventId, int $questionId): Response|JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $this->deleteQuestionHandler->handle($questionId, $eventId);
        } catch (CannotDeleteEntityException $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->deletedResponse();
    }
}
