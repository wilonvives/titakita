<?php

declare(strict_types=1);

namespace TitaKita\Http\Request\Order;

use TitaKita\Http\Request\BaseRequest;
use TitaKita\Validators\CompleteOrderValidator;

class CompleteOrderRequest extends BaseRequest
{
    public function rules(CompleteOrderValidator $orderValidator): array
    {
        return $orderValidator->rules();
    }

    public function messages(): array
    {
        return app(CompleteOrderValidator::class)->messages();
    }
}
