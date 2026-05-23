<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Users;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Repository\Interfaces\UserRepositoryInterface;
use TitaKita\Resources\User\UserResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class GetUserAction extends BaseAction
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function __invoke(int $userId): JsonResponse
    {
        $this->minimumAllowedRole(Role::ADMIN);

        $user = $this->userRepository->findByIdAndAccountId($userId, $this->getAuthenticatedAccountId());

        if (!$user) {
            throw new ResourceNotFoundException();
        }

        return $this->resourceResponse(
            resource: UserResource::class,
            data: $user
        );
    }
}
