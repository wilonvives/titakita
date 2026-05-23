<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Accounts\Vat;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Account\AccountVatSettingResource;
use TitaKita\Services\Application\Handlers\Account\Vat\GetAccountVatSettingHandler;
use Illuminate\Http\JsonResponse;

class GetAccountVatSettingAction extends BaseAction
{
    public function __construct(
        private readonly GetAccountVatSettingHandler $handler,
    ) {
    }

    public function __invoke(int $accountId): JsonResponse
    {
        $this->minimumAllowedRole(Role::ORGANIZER);

        if ($accountId !== $this->getAuthenticatedAccountId()) {
            return $this->errorResponse(__('Unauthorized'));
        }

        $vatSetting = $this->handler->handle($accountId);

        if (!$vatSetting) {
            return $this->jsonResponse(['data' => null]);
        }

        return $this->resourceResponse(AccountVatSettingResource::class, $vatSetting);
    }
}
