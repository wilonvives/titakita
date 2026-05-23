<?php

namespace TitaKita\Http\Actions\TaxesAndFees;

use TitaKita\DomainObjects\TaxAndFeesDomainObject;
use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Application\Handlers\TaxAndFee\DeleteTaxHandler;
use TitaKita\Services\Application\Handlers\TaxAndFee\DTO\DeleteTaxDTO;
use Illuminate\Http\Response;
use Throwable;

class DeleteTaxOrFeeAction extends BaseAction
{
    private DeleteTaxHandler $deleteTaxHandler;

    public function __construct(DeleteTaxHandler $deleteTaxHandler)
    {
        $this->deleteTaxHandler = $deleteTaxHandler;
    }

    /**
     * @throws Throwable
     * @throws ResourceConflictException
     */
    public function __invoke(int $accountId, int $taxOrFeeId): Response
    {
        $this->isActionAuthorized($taxOrFeeId, TaxAndFeesDomainObject::class);

        $this->deleteTaxHandler->handle(new DeleteTaxDTO(
            taxId: $taxOrFeeId,
            accountId: $this->getAuthenticatedAccountId(),
        ));

        return $this->deletedResponse();
    }
}
