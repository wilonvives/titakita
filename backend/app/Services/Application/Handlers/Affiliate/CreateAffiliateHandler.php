<?php

declare(strict_types=1);

namespace TitaKita\Services\Application\Handlers\Affiliate;

use TitaKita\DomainObjects\AffiliateDomainObject;
use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Repository\Interfaces\AffiliateRepositoryInterface;
use TitaKita\Services\Application\Handlers\Affiliate\DTO\UpsertAffiliateDTO;

class CreateAffiliateHandler
{
    public function __construct(
        private readonly AffiliateRepositoryInterface $affiliateRepository,
    )
    {
    }

    /**
     * @throws ResourceConflictException
     */
    public function handle(int $eventId, int $accountId, UpsertAffiliateDTO $dto): AffiliateDomainObject
    {
        $code = strtoupper($dto->code);

        $existingAffiliate = $this->affiliateRepository->findFirstWhere([
            'event_id' => $eventId,
            'code' => $code,
        ]);

        if ($existingAffiliate) {
            throw new ResourceConflictException(__('An affiliate with this code already exists for this event'));
        }

        return $this->affiliateRepository->create([
            'event_id' => $eventId,
            'account_id' => $accountId,
            'name' => $dto->name,
            'code' => $code,
            'email' => $dto->email,
            'status' => $dto->status->value,
        ]);
    }
}
