<?php

namespace TitaKita\Services\Application\Handlers\Admin;

use TitaKita\DataTransferObjects\UpdateAccountConfigurationDTO;
use TitaKita\DomainObjects\AccountConfigurationDomainObject;
use TitaKita\Repository\Interfaces\AccountConfigurationRepositoryInterface;
use TitaKita\Repository\Interfaces\AccountRepositoryInterface;

class UpdateAccountConfigurationHandler
{
    public function __construct(
        private readonly AccountConfigurationRepositoryInterface $configurationRepository,
        private readonly AccountRepositoryInterface $accountRepository,
    )
    {
    }

    public function handle(UpdateAccountConfigurationDTO $dto): AccountConfigurationDomainObject
    {
        $account = $this->accountRepository
            ->loadRelation('configuration')
            ->findById($dto->accountId);

        $data = [
            'application_fees' => $dto->applicationFees,
        ];

        if ($account->getConfiguration()) {
            return $this->configurationRepository->updateFromArray(
                id: $account->getConfiguration()->getId(),
                attributes: $data
            );
        }

        $configuration = $this->configurationRepository->create([
            'name' => 'Account Configuration',
            'is_system_default' => false,
            'application_fees' => $dto->applicationFees,
        ]);

        $this->accountRepository->updateFromArray(
            id: $account->getId(),
            attributes: ['account_configuration_id' => $configuration->getId()]
        );

        return $configuration;
    }
}
