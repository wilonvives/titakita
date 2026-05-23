<?php

namespace TitaKita\Services\Application\Handlers\Question\DTO;

use TitaKita\DataTransferObjects\BaseDTO;

class EditQuestionAnswerDTO extends BaseDTO
{
    public function __construct(
        public int               $questionAnswerId,
        public int               $eventId,
        public null|array|string $answer,
    )
    {
    }
}
