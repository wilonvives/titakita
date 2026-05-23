<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Affiliates;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\Status\AffiliateStatus;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\Affiliate\UpdateAffiliateRequest;
use TitaKita\Resources\Affiliate\AffiliateResource;
use TitaKita\Services\Application\Handlers\Affiliate\DTO\UpsertAffiliateDTO;
use TitaKita\Services\Application\Handlers\Affiliate\UpdateAffiliateHandler;
use Illuminate\Http\JsonResponse;

class UpdateAffiliateAction extends BaseAction
{
    public function __construct(
        private readonly UpdateAffiliateHandler $updateAffiliateHandler
    )
    {
    }

    public function __invoke(UpdateAffiliateRequest $request, int $eventId, int $affiliateId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $affiliate = $this->updateAffiliateHandler->handle(
            $affiliateId,
            $eventId,
            new UpsertAffiliateDTO(
                name: $request->input('name'),
                code: '', // Code cannot be updated
                email: $request->input('email'),
                status: AffiliateStatus::from($request->input('status')),
            )
        );

        return $this->resourceResponse(
            resource: AffiliateResource::class,
            data: $affiliate
        );
    }
}
