<?php

namespace TitaKita\Services\Application\Handlers\PromoCode\DTO;

use TitaKita\DataTransferObjects\BaseDTO;

class DeletePromoCodeDTO extends BaseDTO
{
    public function __construct(
        public int $promo_code_id,
        public int $event_id,
        public int $user_id,
    )
    {
    }
}
