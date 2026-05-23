<?php

namespace TitaKita\Http\Actions\Questions;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\DomainObjects\ProductPriceDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\QuestionRepositoryInterface;
use TitaKita\Resources\Question\QuestionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetQuestionsAction extends BaseAction
{
    private QuestionRepositoryInterface $questionRepository;

    public function __construct(QuestionRepositoryInterface $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    public function __invoke(Request $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $questions = $this->questionRepository
            ->loadRelation(
                new Relationship(ProductDomainObject::class, [
                    new Relationship(ProductPriceDomainObject::class)
                ])
            )
            ->findByEventId($eventId);

        return $this->resourceResponse(QuestionResource::class, $questions);
    }
}
