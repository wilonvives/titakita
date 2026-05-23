<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Admin\Accounts;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Account\AdminAccountDetailResource;
use TitaKita\Services\Application\Handlers\Admin\GetAccountHandler;
use TitaKita\Services\Application\Handlers\Admin\UpdateAccountMessagingTierHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateAccountMessagingTierAction extends BaseAction
{
    public function __construct(
        private readonly UpdateAccountMessagingTierHandler $handler,
        private readonly GetAccountHandler $getAccountHandler,
    ) {
    }

    public function __invoke(Request $request, int $accountId): JsonResponse
    {
        $this->minimumAllowedRole(Role::SUPERADMIN);

        $validated = $request->validate([
            'messaging_tier_id' => 'required|integer|exists:account_messaging_tiers,id',
        ]);

        $this->handler->handle($accountId, $validated['messaging_tier_id']);

        $account = $this->getAccountHandler->handle($accountId);

        return $this->jsonResponse(new AdminAccountDetailResource($account), wrapInData: true);
    }
}
