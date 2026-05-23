<?php

namespace TitaKita\Http\Request\User;

use TitaKita\Http\Request\BaseRequest;

class EmailChangeRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'token' => 'required|string',
        ];
    }
}
