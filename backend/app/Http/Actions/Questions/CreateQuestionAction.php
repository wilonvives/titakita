<?php

namespace TitaKita\Http\Actions\Questions;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\Questions\UpsertQuestionRequest;
use TitaKita\Http\ResponseCodes;
use TitaKita\Resources\Question\QuestionResource;
use TitaKita\Services\Application\Handlers\Question\CreateQuestionHandler;
use TitaKita\Services\Application\Handlers\Question\DTO\UpsertQuestionDTO;
use Illuminate\Http\JsonResponse;

class CreateQuestionAction extends BaseAction
{
    private CreateQuestionHandler $createQuestionHandler;

    public function __construct(CreateQuestionHandler $createQuestionHandler)
    {
        $this->createQuestionHandler = $createQuestionHandler;
    }

    public function __invoke(UpsertQuestionRequest $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $question = $this->createQuestionHandler->handle(UpsertQuestionDTO::fromArray([
            'title' => $request->input('title'),
            'type' => $request->input('type'),
            'required' => $request->boolean('required'),
            'options' => $request->input('options'),
            'event_id' => $eventId,
            'product_ids' => $request->input('product_ids'),
            'belongs_to' => $request->input('belongs_to'),
            'is_hidden' => $request->boolean('is_hidden'),
            'description' => $request->input('description'),
        ]));

        return $this->resourceResponse(
            resource: QuestionResource::class,
            data: $question,
            statusCode: ResponseCodes::HTTP_CREATED
        );
    }
}
