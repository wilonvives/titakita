<?php

namespace TitaKita\Http\Actions\PromoCodes;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\PromoCodeDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\DTO\QueryParamsDTO;
use TitaKita\Repository\Interfaces\PromoCodeRepositoryInterface;
use TitaKita\Resources\PromoCode\PromoCodeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetPromoCodesAction extends BaseAction
{
    private PromoCodeRepositoryInterface $promoCodeRepository;

    public function __construct(PromoCodeRepositoryInterface $promoCodeRepository)
    {
        $this->promoCodeRepository = $promoCodeRepository;
    }

    public function __invoke(Request $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $codes = $this->promoCodeRepository->findByEventId($eventId, QueryParamsDTO::fromArray($request->query->all()));

        return $this->filterableResourceResponse(
            resource: PromoCodeResource::class,
            data: $codes,
            domainObject: PromoCodeDomainObject::class
        );
    }
}
