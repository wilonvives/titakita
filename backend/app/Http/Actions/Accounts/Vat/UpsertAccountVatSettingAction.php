<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Accounts\Vat;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Account\AccountVatSettingResource;
use TitaKita\Services\Application\Handlers\Account\Vat\DTO\UpsertAccountVatSettingDTO;
use TitaKita\Services\Application\Handlers\Account\Vat\UpsertAccountVatSettingHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpsertAccountVatSettingAction extends BaseAction
{
    public function __construct(
        private readonly UpsertAccountVatSettingHandler $handler,
    ) {
    }

    public function __invoke(Request $request, int $accountId): JsonResponse
    {
        $this->minimumAllowedRole(Role::ADMIN);

        if ($accountId !== $this->getAuthenticatedAccountId()) {
            return $this->errorResponse(__('Unauthorized'));
        }

        $validated = $request->validate([
            'vat_registered' => 'required|boolean',
            'vat_number' => 'nullable|string|max:20',
        ]);

        $vatSetting = $this->handler->handle(new UpsertAccountVatSettingDTO(
            accountId: $accountId,
            vatRegistered: $validated['vat_registered'],
            vatNumber: $validated['vat_number'] ?? null,
        ));

        return $this->resourceResponse(AccountVatSettingResource::class, $vatSetting);
    }
}
