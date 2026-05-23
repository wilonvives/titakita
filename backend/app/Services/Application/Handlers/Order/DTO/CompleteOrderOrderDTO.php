<?php

namespace TitaKita\Services\Application\Handlers\Order\DTO;

use TitaKita\DataTransferObjects\Attributes\CollectionOf;
use TitaKita\DataTransferObjects\BaseDTO;
use Illuminate\Support\Collection;

class CompleteOrderOrderDTO extends BaseDTO
{
    /**
     * @param string $first_name
     * @param string $last_name
     * @param string $email
     * @param Collection<OrderQuestionsDTO>|null $questions
     * @param array|null $address
     * @param bool $opted_into_marketing
     */
    public function __construct(
        public readonly string      $first_name,
        public readonly string      $last_name,
        public readonly string      $email,
        #[CollectionOf(OrderQuestionsDTO::class)]
        public readonly ?Collection $questions,
        public readonly ?array      $address = [],
        public readonly bool        $opted_into_marketing = false,
    )
    {
    }
}
