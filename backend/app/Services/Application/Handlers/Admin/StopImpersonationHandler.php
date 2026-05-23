<?php

namespace TitaKita\Services\Application\Handlers\Admin;

use TitaKita\Models\User;
use TitaKita\Services\Application\Handlers\Admin\DTO\StopImpersonationDTO;
use Illuminate\Auth\AuthManager;

class StopImpersonationHandler
{
    public function __construct(
        private readonly AuthManager $authManager,
    )
    {
    }

    public function handle(StopImpersonationDTO $dto): string
    {
        $impersonator = User::findOrFail($dto->impersonatorId);
        $impersonatorAccountId = $impersonator->accounts()->first()->id;

        return $this->authManager->claims([
            'account_id' => $impersonatorAccountId,
            'is_impersonating' => false,
            'impersonator_id' => null,
        ])->login($impersonator);
    }
}
