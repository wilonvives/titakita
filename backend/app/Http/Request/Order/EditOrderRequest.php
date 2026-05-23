<?php

namespace TitaKita\Http\Request\Order;

use TitaKita\Http\Request\BaseRequest;
use TitaKita\Validators\Rules\RulesHelper;

class EditOrderRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email' => RulesHelper::REQUIRED_EMAIL,
            'first_name' => RulesHelper::REQUIRED_STRING,
            'last_name' => RulesHelper::REQUIRED_STRING,
            'notes' => RulesHelper::OPTIONAL_TEXT_MEDIUM_LENGTH,
        ];
    }
}
