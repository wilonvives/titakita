<?php

namespace TitaKita\Services\Application\Handlers\Waitlist;

use TitaKita\DomainObjects\EventSettingDomainObject;
use TitaKita\Repository\Eloquent\Value\Relationship;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\EventSettingsRepositoryInterface;
use TitaKita\Services\Application\Handlers\Waitlist\DTO\OfferWaitlistEntryDTO;
use TitaKita\Services\Domain\Waitlist\ProcessWaitlistService;
use Illuminate\Support\Collection;

class OfferWaitlistEntryHandler
{
    public function __construct(
        private readonly ProcessWaitlistService           $processWaitlistService,
        private readonly EventSettingsRepositoryInterface $eventSettingsRepository,
        private readonly EventRepositoryInterface         $eventRepository,
    )
    {
    }

    public function handle(OfferWaitlistEntryDTO $dto): Collection
    {
        $eventSettings = $this->eventSettingsRepository->findFirstWhere([
            'event_id' => $dto->event_id,
        ]);

        $event = $this->eventRepository
            ->loadRelation(new Relationship(EventSettingDomainObject::class))
            ->findById($dto->event_id);

        if ($dto->entry_id !== null) {
            return $this->processWaitlistService->offerSpecificEntry(
                entryId: $dto->entry_id,
                eventId: $dto->event_id,
                event: $event,
                eventSettings: $eventSettings,
            );
        }

        return $this->processWaitlistService->offerToNext(
            productPriceId: $dto->product_price_id,
            quantity: $dto->quantity,
            event: $event,
            eventSettings: $eventSettings,
        );
    }
}
