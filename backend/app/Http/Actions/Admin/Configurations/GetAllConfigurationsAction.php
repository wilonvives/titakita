<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Admin\Configurations;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Repository\Interfaces\AccountConfigurationRepositoryInterface;
use TitaKita\Resources\Account\AccountConfigurationResource;
use Illuminate\Http\JsonResponse;

class GetAllConfigurationsAction extends BaseAction
{
    public function __construct(
        private readonly AccountConfigurationRepositoryInterface $repository,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $this->minimumAllowedRole(Role::SUPERADMIN);

        $configurations = $this->repository->all();

        return $this->jsonResponse(
            AccountConfigurationResource::collection($configurations),
            wrapInData: true
        );
    }
}
