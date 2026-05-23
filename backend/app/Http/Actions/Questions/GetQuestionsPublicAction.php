<?php

namespace TitaKita\Http\Actions\Questions;

use TitaKita\DomainObjects\Generated\QuestionDomainObjectAbstract;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Repository\Interfaces\QuestionRepositoryInterface;
use TitaKita\Resources\Question\QuestionResourcePublic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetQuestionsPublicAction extends BaseAction
{
    private QuestionRepositoryInterface $questionRepository;

    public function __construct(QuestionRepositoryInterface $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    public function __invoke(Request $request, int $eventId): JsonResponse
    {
        $questions = $this->questionRepository
            ->loadRelation(ProductDomainObject::class)
            ->findWhere([
                QuestionDomainObjectAbstract::EVENT_ID => $eventId,
                QuestionDomainObjectAbstract::IS_HIDDEN => false,
            ])
            ->sortBy(fn(QuestionDomainObjectAbstract $question) => $question->getOrder());

        return $this->resourceResponse(QuestionResourcePublic::class, $questions);
    }
}
