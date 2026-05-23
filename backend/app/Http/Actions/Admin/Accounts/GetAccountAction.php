<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Admin\Accounts;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Account\AdminAccountDetailResource;
use TitaKita\Services\Application\Handlers\Admin\GetAccountHandler;
use Illuminate\Http\JsonResponse;

class GetAccountAction extends BaseAction
{
    public function __construct(
        private readonly GetAccountHandler $handler,
    )
    {
    }

    public function __invoke(int $accountId): JsonResponse
    {
        $this->minimumAllowedRole(Role::SUPERADMIN);

        $account = $this->handler->handle($accountId);

        return $this->jsonResponse(new AdminAccountDetailResource($account), wrapInData: true);
    }
}
