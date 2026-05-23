<?php

namespace TitaKita\Http\Actions\Users;

use TitaKita\Http\Actions\Auth\BaseAuthAction;
use TitaKita\Resources\User\UserResource;
use Illuminate\Http\JsonResponse;

class GetMeAction extends BaseAuthAction
{
    public function __invoke(): JsonResponse
    {
        return $this->resourceResponse(
            resource: UserResource::class,
            data: $this->getAuthenticatedUser(),
        );
    }
}
