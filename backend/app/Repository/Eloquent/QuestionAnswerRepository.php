<?php

namespace TitaKita\Repository\Eloquent;


use TitaKita\DomainObjects\QuestionAnswerDomainObject;
use TitaKita\Models\QuestionAnswer;
use TitaKita\Repository\Interfaces\QuestionAnswerRepositoryInterface;

/**
 * @extends BaseRepository<QuestionAnswerDomainObject>
 */
class QuestionAnswerRepository extends BaseRepository implements QuestionAnswerRepositoryInterface
{
    protected function getModel(): string
    {
        return QuestionAnswer::class;
    }

    public function getDomainObject(): string
    {
        return QuestionAnswerDomainObject::class;
    }
}
