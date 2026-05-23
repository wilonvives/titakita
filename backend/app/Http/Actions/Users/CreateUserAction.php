<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Users;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\User\CreateUserRequest;
use TitaKita\Http\ResponseCodes;
use TitaKita\Resources\User\UserResource;
use TitaKita\Services\Application\Handlers\User\CreateUserHandler;
use TitaKita\Services\Application\Handlers\User\DTO\CreateUserDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateUserAction extends BaseAction
{
    public function __construct(
        private readonly CreateUserHandler $createUserHandler
    )
    {
    }

    /**
     * @throws ValidationException|Throwable
     */
    public function __invoke(CreateUserRequest $request): JsonResponse
    {
        $this->minimumAllowedRole(Role::ADMIN);

        $data = array_merge($request->validated(), [
            'invited_by' => $this->getAuthenticatedUser()->getId(),
            'account_id' => $this->getAuthenticatedAccountId(),
        ]);

        try {
            $user = $this->createUserHandler->handle(CreateUserDTO::from($data));
        } catch (ResourceConflictException $e) {
            throw ValidationException::withMessages([
                'email' => $e->getMessage(),
            ]);
        }

        return $this->resourceResponse(
            resource: UserResource::class,
            data: $user,
            statusCode: ResponseCodes::HTTP_CREATED
        );
    }
}
