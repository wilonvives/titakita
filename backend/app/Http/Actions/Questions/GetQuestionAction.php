<?php

namespace TitaKita\Http\Actions\Questions;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\Generated\QuestionDomainObjectAbstract;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\Exceptions\ResourceNotFoundException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Repository\Interfaces\QuestionRepositoryInterface;
use TitaKita\Resources\Question\QuestionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetQuestionAction extends BaseAction
{
    private QuestionRepositoryInterface $questionRepository;

    public function __construct(QuestionRepositoryInterface $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    /**
     * @throws ResourceNotFoundException
     */
    public function __invoke(Request $request, int $eventId, int $questionId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $question = $this->questionRepository
            ->loadRelation(ProductDomainObject::class)
            ->findFirstWhere([
                QuestionDomainObjectAbstract::ID => $questionId,
                QuestionDomainObjectAbstract::EVENT_ID => $eventId,
            ]);

        if ($question === null) {
            throw new ResourceNotFoundException(__('Question not found'));
        }

        return $this->resourceResponse(QuestionResource::class, $question);
    }
}
