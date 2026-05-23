<?php

namespace TitaKita\Http\Actions\Users;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\DomainObjects\Status\UserStatus;
use TitaKita\DomainObjects\UserDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Repository\Interfaces\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class DeleteInvitationAction extends BaseAction
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function __invoke(int $userId): JsonResponse|Response
    {
        $this->isActionAuthorized($userId, UserDomainObject::class, Role::ADMIN);

        $user = $this->userRepository->findByIdAndAccountId($userId, $this->getAuthenticatedAccountId());

        if ($user->getCurrentAccountUser()?->getStatus() !== UserStatus::INVITED->name) {
            return $this->errorResponse(__('No invitation found for this user.'));
        }

        $this->userRepository->deleteWhere([
            'id' => $userId,
        ]);

        return $this->noContentResponse();
    }
}
