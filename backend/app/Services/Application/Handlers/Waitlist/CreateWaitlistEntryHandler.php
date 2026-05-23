<?php

namespace TitaKita\Services\Application\Handlers\Waitlist;

use TitaKita\DomainObjects\WaitlistEntryDomainObject;
use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Exceptions\ResourceNotFoundException;
use TitaKita\Repository\Interfaces\EventSettingsRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductPriceRepositoryInterface;
use TitaKita\Repository\Interfaces\ProductRepositoryInterface;
use TitaKita\Services\Application\Handlers\Waitlist\DTO\CreateWaitlistEntryDTO;
use TitaKita\Services\Domain\Waitlist\CreateWaitlistEntryService;

class CreateWaitlistEntryHandler
{
    public function __construct(
        private readonly CreateWaitlistEntryService       $createWaitlistEntryService,
        private readonly EventSettingsRepositoryInterface $eventSettingsRepository,
        private readonly ProductPriceRepositoryInterface  $productPriceRepository,
        private readonly ProductRepositoryInterface       $productRepository,
    )
    {
    }

    /**
     * @throws ResourceConflictException
     * @throws ResourceNotFoundException
     */
    public function handle(CreateWaitlistEntryDTO $dto): WaitlistEntryDomainObject
    {
        $eventSettings = $this->eventSettingsRepository->findFirstWhere([
            'event_id' => $dto->event_id,
        ]);

        $productPrice = $this->productPriceRepository->findById($dto->product_price_id);

        $product = $this->productRepository->findFirstWhere([
            'id' => $productPrice->getProductId(),
            'event_id' => $dto->event_id,
        ]);

        if ($product === null) {
            throw new ResourceNotFoundException(__('Product not found for this event'));
        }

        return $this->createWaitlistEntryService->createEntry($dto, $eventSettings, $product);
    }
}
