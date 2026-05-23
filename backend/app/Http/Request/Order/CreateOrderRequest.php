<?php

declare(strict_types=1);

namespace TitaKita\Http\Request\Order;

use TitaKita\Http\Request\BaseRequest;
use TitaKita\Services\Domain\Order\OrderCreateRequestValidationService;

class CreateOrderRequest extends BaseRequest
{
    /**
     * @see OrderCreateRequestValidationService
     */
    public function rules(): array
    {
        return [];
    }
}
