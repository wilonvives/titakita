<?php

namespace TitaKita\Http\Actions\Accounts;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\Account\UpdateAccountRequest;
use TitaKita\Resources\Account\AccountResource;
use TitaKita\Services\Application\Handlers\Account\DTO\UpdateAccountDTO;
use TitaKita\Services\Application\Handlers\Account\UpdateAccountHanlder;
use Illuminate\Http\JsonResponse;

class UpdateAccountAction extends BaseAction
{
    private UpdateAccountHanlder $updateAccountHandler;

    public function __construct(UpdateAccountHanlder $updateAccountHandler)
    {
        $this->updateAccountHandler = $updateAccountHandler;
    }

    public function __invoke(UpdateAccountRequest $request): JsonResponse
    {
        $this->minimumAllowedRole(Role::ADMIN);

        $authUser = $this->getAuthenticatedUser();

        $payload = array_merge($request->validated(), [
            'account_id' => $this->getAuthenticatedAccountId(),
            'updated_by_user_id' => $authUser->getId(),
        ]);

        $account = $this->updateAccountHandler->handle(UpdateAccountDTO::fromArray($payload));

        return $this->resourceResponse(AccountResource::class, $account);
    }
}
