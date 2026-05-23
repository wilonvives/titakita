<?php

declare(strict_types=1);

namespace TitaKita\Services\Application\Handlers\Admin;

use TitaKita\Exceptions\CannotDeleteEntityException;
use TitaKita\Repository\Interfaces\AccountConfigurationRepositoryInterface;

class DeleteConfigurationHandler
{
    public function __construct(
        private readonly AccountConfigurationRepositoryInterface $repository,
    ) {
    }

    /**
     * @throws CannotDeleteEntityException
     */
    public function handle(int $configurationId): void
    {
        $configuration = $this->repository->findById($configurationId);

        if ($configuration->getIsSystemDefault()) {
            throw new CannotDeleteEntityException(
                __('The system default configuration cannot be deleted.')
            );
        }

        $this->repository->deleteById($configurationId);
    }
}
