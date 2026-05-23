<?php

namespace TitaKita\Http\Request\Auth;

use TitaKita\Http\Request\BaseRequest;
use TitaKita\Validators\Rules\RulesHelper;

class AcceptInvitationRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'first_name' => RulesHelper::REQUIRED_STRING,
            'last_name' => RulesHelper::STRING,
            'password' => 'required|string|min:8|confirmed',
            'timezone' => ['required', 'timezone:all'],
        ];
    }
}
