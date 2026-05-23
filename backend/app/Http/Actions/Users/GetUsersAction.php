<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Users;

use TitaKita\DomainObjects\AccountUserDomainObject;
use TitaKita\DomainObjects\Enums\Role;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\UserRepositoryInterface;
use TitaKita\Resources\User\UserResource;
use Illuminate\Http\JsonResponse;

class GetUsersAction extends BaseAction
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function __invoke(): JsonResponse
    {
        $this->minimumAllowedRole(Role::ADMIN);

        return $this->resourceResponse(
            UserResource::class,
            $this->userRepository
                ->loadRelation(new Relationship(domainObject: AccountUserDomainObject::class, name: 'currentAccountUser'))
                ->findUsersByAccountId($this->getAuthenticatedAccountId()),
        );
    }
}
