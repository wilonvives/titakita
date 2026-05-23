<?php

namespace TitaKita\Http\Actions\Users;

use TitaKita\DomainObjects\UserDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\User\UserResource;
use TitaKita\Services\Application\Handlers\User\CancelEmailChangeHandler;
use TitaKita\Services\Application\Handlers\User\DTO\CancelEmailChangeDTO;
use Illuminate\Http\JsonResponse;

class CancelEmailChangeAction extends BaseAction
{
    private CancelEmailChangeHandler $cancelEmailChangeHandler;

    public function __construct(CancelEmailChangeHandler $cancelEmailChangeHandler)
    {
        $this->cancelEmailChangeHandler = $cancelEmailChangeHandler;
    }

    public function __invoke(int $userId): JsonResponse
    {
        $this->isActionAuthorized($userId, UserDomainObject::class);

        $user = $this->cancelEmailChangeHandler->handle(
            new CancelEmailChangeDTO(
                userId: $userId,
                accountId: $this->getAuthenticatedAccountId(),
            )
        );

        return $this->resourceResponse(UserResource::class, $user);
    }
}
