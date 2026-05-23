<?php

namespace TitaKita\Services\Application\Handlers\TaxAndFee;

use TitaKita\DomainObjects\Generated\TaxAndFeesDomainObjectAbstract;
use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Repository\Interfaces\TaxAndFeeRepositoryInterface;
use TitaKita\Services\Application\Handlers\TaxAndFee\DTO\DeleteTaxDTO;
use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Throwable;

readonly class DeleteTaxHandler
{
    public function __construct(
        private TaxAndFeeRepositoryInterface $taxRepository,
        private LoggerInterface              $logger,
        private DatabaseManager              $databaseManager
    )
    {
    }

    /**
     * @throws ResourceConflictException
     * @throws Throwable
     */
    public function handle(DeleteTaxDTO $taxData): void
    {
        $this->databaseManager->transaction(function () use ($taxData) {
            $tax = $this->taxRepository->findFirstWhere([
                TaxAndFeesDomainObjectAbstract::ID => $taxData->taxId,
                TaxAndFeesDomainObjectAbstract::ACCOUNT_ID => $taxData->accountId,
            ]);

            if (!$tax) {
                throw new ResourceNotFoundException();
            }

            $this->taxRepository->deleteWhere([
                TaxAndFeesDomainObjectAbstract::ID => $taxData->taxId,
                TaxAndFeesDomainObjectAbstract::ACCOUNT_ID => $taxData->accountId,
            ]);

            $this->logger->info('Deleted tax', [
                'tax_id' => $taxData->taxId,
                'account_id' => $taxData->accountId,
            ]);
        });
    }
}
