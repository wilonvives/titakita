<?php

namespace TitaKita\Http\Actions\Questions;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\Questions\SortQuestionsRequest;
use TitaKita\Services\Application\Handlers\Question\SortQuestionsHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class SortQuestionsAction extends BaseAction
{
    public function __construct(
        private readonly SortQuestionsHandler $sortQuestionsHandler
    )
    {
    }

    public function __invoke(SortQuestionsRequest $request, int $eventId): Response|JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $this->sortQuestionsHandler->handle(
                $eventId,
                $request->validated(),
            );
        } catch (ResourceNotFoundException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->noContentResponse();
    }
}
