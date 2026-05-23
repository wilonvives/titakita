<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\QuestionAndAnswerViewDomainObject;
use TitaKita\Models\QuestionAndAnswerView;
use TitaKita\Repository\Interfaces\QuestionAndAnswerViewRepositoryInterface;

/**
 * @extends BaseRepository<QuestionAndAnswerViewDomainObject>
 */
class QuestionAndAnswerViewRepository extends BaseRepository implements QuestionAndAnswerViewRepositoryInterface
{
    protected function getModel(): string
    {
        return QuestionAndAnswerView::class;
    }

    public function getDomainObject(): string
    {
        return QuestionAndAnswerViewDomainObject::class;
    }
}
