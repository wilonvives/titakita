<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Admin\Accounts;

use TitaKita\DataTransferObjects\UpdateAccountConfigurationDTO;
use TitaKita\DomainObjects\Enums\Role;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Account\AccountConfigurationResource;
use TitaKita\Services\Application\Handlers\Admin\UpdateAccountConfigurationHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateAccountConfigurationAction extends BaseAction
{
    public function __construct(
        private readonly UpdateAccountConfigurationHandler $handler,
    )
    {
    }

    public function __invoke(Request $request, int $accountId): JsonResponse
    {
        $this->minimumAllowedRole(Role::SUPERADMIN);

        $validated = $request->validate([
            'application_fees' => 'required|array',
            'application_fees.fixed' => 'required|numeric|min:0',
            'application_fees.percentage' => 'required|numeric|min:0|max:100',
        ]);

        $configuration = $this->handler->handle(new UpdateAccountConfigurationDTO(
            accountId: $accountId,
            applicationFees: $validated['application_fees'],
        ));

        return $this->resourceResponse(
            resource: AccountConfigurationResource::class,
            data: $configuration
        );
    }
}
