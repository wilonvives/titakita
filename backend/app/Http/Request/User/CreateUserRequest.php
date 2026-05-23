<?php

declare(strict_types=1);

namespace TitaKita\Http\Request\User;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\Http\Request\BaseRequest;
use Illuminate\Validation\Rule;

class CreateUserRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'first_name' => 'required|min:1',
            'last_name' => 'min:1|nullable',
            'role' => ['required', Rule::in(Role::getAssignableRoles())],
            'email' => [
                'required',
                'email',
            ],
        ];
    }
}
