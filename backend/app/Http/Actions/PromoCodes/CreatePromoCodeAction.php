<?php

namespace TitaKita\Http\Actions\PromoCodes;

use TitaKita\DomainObjects\Enums\PromoCodeDiscountTypeEnum;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\PromoCode\CreateUpdatePromoCodeRequest;
use TitaKita\Http\ResponseCodes;
use TitaKita\Resources\PromoCode\PromoCodeResource;
use TitaKita\Services\Application\Handlers\PromoCode\CreatePromoCodeHandler;
use TitaKita\Services\Application\Handlers\PromoCode\DTO\UpsertPromoCodeDTO;
use TitaKita\Services\Domain\Product\Exception\UnrecognizedProductIdException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CreatePromoCodeAction extends BaseAction
{
    private CreatePromoCodeHandler $createPromoCodeHandler;

    public function __construct(CreatePromoCodeHandler $promoCodeHandler)
    {
        $this->createPromoCodeHandler = $promoCodeHandler;
    }

    /**
     * @throws ValidationException
     */
    public function __invoke(CreateUpdatePromoCodeRequest $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $promoCode = $this->createPromoCodeHandler->handle($eventId, new UpsertPromoCodeDTO(
                code: strtolower($request->input('code')),
                event_id: $eventId,
                applicable_product_ids: $request->input('applicable_product_ids'),
                discount_type: PromoCodeDiscountTypeEnum::fromName($request->input('discount_type')),
                discount: $request->float('discount'),
                expiry_date: $request->input('expiry_date'),
                max_allowed_usages: $request->input('max_allowed_usages'),
            ));
        } catch (ResourceConflictException $e) {
            throw ValidationException::withMessages([
                'code' => $e->getMessage(),
            ]);
        } catch (UnrecognizedProductIdException $e) {
            throw ValidationException::withMessages([
                'applicable_product_ids' => $e->getMessage(),
            ]);
        }

        return $this->resourceResponse(
            PromoCodeResource::class,
            $promoCode,
            ResponseCodes::HTTP_CREATED
        );
    }
}
