<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Admin;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Resources\Admin\AccountMessagingTierResource;
use TitaKita\Repository\Interfaces\AccountMessagingTierRepositoryInterface;
use Illuminate\Http\JsonResponse;

class GetMessagingTiersAction extends BaseAction
{
    public function __construct(
        private readonly AccountMessagingTierRepositoryInterface $messagingTierRepository,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $this->minimumAllowedRole(Role::SUPERADMIN);

        $tiers = $this->messagingTierRepository->all();

        return $this->resourceResponse(
            resource: AccountMessagingTierResource::class,
            data: $tiers
        );
    }
}
