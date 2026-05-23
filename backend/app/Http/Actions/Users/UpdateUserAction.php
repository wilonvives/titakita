<?php

namespace TitaKita\Http\Actions\Users;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\DomainObjects\UserDomainObject;
use TitaKita\Exceptions\CannotUpdateResourceException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\User\UpdateUserRequest;
use TitaKita\Resources\User\UserResource;
use TitaKita\Services\Application\Handlers\User\DTO\UpdateUserDTO;
use TitaKita\Services\Application\Handlers\User\UpdateUserHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class UpdateUserAction extends BaseAction
{
    private UpdateUserHandler $updateUserHandler;

    public function __construct(UpdateUserHandler $updateUserHandler)
    {
        $this->updateUserHandler = $updateUserHandler;
    }

    /**
     * @throws ValidationException|Throwable
     */
    public function __invoke(UpdateUserRequest $request, int $userId): JsonResponse
    {
        $this->isActionAuthorized(
            entityId: $userId,
            entityType: UserDomainObject::class,
            minimumRole: Role::ADMIN
        );

        $authenticatedUser = $this->getAuthenticatedUser();

        $userData = $request->validated() + [
                'id' => $userId,
                'account_id' => $this->getAuthenticatedAccountId(),
                'updated_by_user_id' => $authenticatedUser->getId(),
            ];

        try {
            $user = $this->updateUserHandler->handle(UpdateUserDTO::fromArray($userData));
        } catch (CannotUpdateResourceException $e) {
            throw ValidationException::withMessages([
                'role' => $e->getMessage(),
            ]);
        }

        return $this->resourceResponse(UserResource::class, $user);
    }
}
