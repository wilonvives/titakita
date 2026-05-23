<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Admin\Accounts;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Account\AdminAccountResource;
use TitaKita\Services\Application\Handlers\Admin\DTO\GetAllAccountsDTO;
use TitaKita\Services\Application\Handlers\Admin\GetAllAccountsHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetAllAccountsAction extends BaseAction
{
    public function __construct(
        private readonly GetAllAccountsHandler $handler,
    )
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $this->minimumAllowedRole(Role::SUPERADMIN);

        $accounts = $this->handler->handle(new GetAllAccountsDTO(
            perPage: min((int)$request->query('per_page', 20), 100),
            search: $request->query('search'),
        ));

        return $this->resourceResponse(
            resource: AdminAccountResource::class,
            data: $accounts
        );
    }
}
