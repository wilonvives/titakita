<?php

namespace TitaKita\Http\Actions\Users;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\DomainObjects\Status\UserStatus;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Repository\Interfaces\UserRepositoryInterface;
use TitaKita\Services\Domain\User\SendUserInvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ResendInvitationAction extends BaseAction
{
    private UserRepositoryInterface $userRepository;

    private SendUserInvitationService $invitationService;

    public function __construct(UserRepositoryInterface $userRepository, SendUserInvitationService $invitationService)
    {
        $this->userRepository = $userRepository;
        $this->invitationService = $invitationService;
    }

    public function __invoke(int $userId): JsonResponse|Response
    {
        $this->minimumAllowedRole(Role::ADMIN);

        $user = $this->userRepository->findByIdAndAccountId($userId, $this->getAuthenticatedAccountId());

        if ($user->getCurrentAccountUser()?->getStatus() !== UserStatus::INVITED->name) {
            return $this->errorResponse(__('User status is not Invited'));
        }

        $this->invitationService->sendInvitation($user, $this->getAuthenticatedAccountId());

        return $this->noContentResponse();
    }
}
