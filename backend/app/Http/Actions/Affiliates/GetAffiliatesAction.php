<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Affiliates;

use TitaKita\DomainObjects\AffiliateDomainObject;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\DTO\QueryParamsDTO;
use TitaKita\Repository\Interfaces\AffiliateRepositoryInterface;
use TitaKita\Resources\Affiliate\AffiliateResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetAffiliatesAction extends BaseAction
{
    public function __construct(private readonly AffiliateRepositoryInterface $affiliateRepository)
    {
    }

    public function __invoke(Request $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $affiliates = $this->affiliateRepository->findByEventId($eventId, QueryParamsDTO::fromArray($request->query->all()));

        return $this->filterableResourceResponse(
            resource: AffiliateResource::class,
            data: $affiliates,
            domainObject: AffiliateDomainObject::class
        );
    }
}
