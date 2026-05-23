<?php

namespace TitaKita\Http\Actions\TaxesAndFees;

use TitaKita\DomainObjects\AccountDomainObject;
use TitaKita\Exceptions\ResourceNameAlreadyExistsException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\TaxOrFee\CreateTaxOrFeeRequest;
use TitaKita\Resources\Tax\TaxAndFeeResource;
use TitaKita\Services\Application\Handlers\TaxAndFee\CreateTaxOrFeeHandler;
use TitaKita\Services\Application\Handlers\TaxAndFee\DTO\UpsertTaxDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CreateTaxOrFeeAction extends BaseAction
{
    private CreateTaxOrFeeHandler $taxHandler;

    public function __construct(CreateTaxOrFeeHandler $taxHandler)
    {
        $this->taxHandler = $taxHandler;
    }

    /**
     * @throws ValidationException
     */
    public function __invoke(CreateTaxOrFeeRequest $request, int $accountId): JsonResponse
    {
        $this->isActionAuthorized($accountId, AccountDomainObject::class);

        try {
            $payload = array_merge($request->validated(), [
                'account_id' => $this->getAuthenticatedAccountId(),
            ]);

            $tax = $this->taxHandler->handle(UpsertTaxDTO::fromArray($payload));
        } catch (ResourceNameAlreadyExistsException $e) {
            throw ValidationException::withMessages([
                'name' => $e->getMessage(),
            ]);
        }

        return $this->resourceResponse(TaxAndFeeResource::class, $tax);
    }
}
