<?php

namespace TitaKita\Http\Request\User;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\DomainObjects\Status\UserStatus;
use TitaKita\Http\Request\BaseRequest;
use TitaKita\Validators\Rules\RulesHelper;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'first_name' => RulesHelper::STRING,
            'last_name' => RulesHelper::STRING,
            'status' => Rule::in([UserStatus::INACTIVE->name, UserStatus::ACTIVE->name]), // don't allow INVITED
            'role' => Rule::in(Role::getAssignableRoles())
        ];
    }
}
