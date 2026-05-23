<?php

declare(strict_types=1);

namespace TitaKita\Services\Application\Handlers\Admin;

use TitaKita\Repository\Interfaces\AccountConfigurationRepositoryInterface;
use TitaKita\Repository\Interfaces\AccountRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AssignConfigurationHandler
{
    public function __construct(
        private readonly AccountRepositoryInterface $accountRepository,
        private readonly AccountConfigurationRepositoryInterface $configurationRepository,
    ) {
    }

    /**
     * @throws ModelNotFoundException
     */
    public function handle(int $accountId, int $configurationId): void
    {
        $this->configurationRepository->findById($configurationId);

        $this->accountRepository->updateFromArray(
            id: $accountId,
            attributes: ['account_configuration_id' => $configurationId]
        );
    }
}
