<?php

namespace TitaKita\Services\Domain\Waitlist;

use TitaKita\DomainObjects\EventSettingDomainObject;
use TitaKita\DomainObjects\ProductDomainObject;
use TitaKita\DomainObjects\Status\WaitlistEntryStatus;
use TitaKita\DomainObjects\WaitlistEntryDomainObject;
use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Helper\EmailHelper;
use TitaKita\Jobs\Waitlist\SendWaitlistConfirmationEmailJob;
use TitaKita\Repository\Interfaces\WaitlistEntryRepositoryInterface;
use TitaKita\Services\Application\Handlers\Waitlist\DTO\CreateWaitlistEntryDTO;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Str;

class CreateWaitlistEntryService
{
    public function __construct(
        private readonly WaitlistEntryRepositoryInterface $waitlistEntryRepository,
        private readonly DatabaseManager                  $databaseManager,
    )
    {
    }

    /**
     * @throws ResourceConflictException
     */
    public function createEntry(
        CreateWaitlistEntryDTO    $dto,
        EventSettingDomainObject  $eventSettings,
        ProductDomainObject       $product,
    ): WaitlistEntryDomainObject
    {
        $this->validateWaitlistEnabled($product);

        /** @var WaitlistEntryDomainObject $entry */
        $entry = $this->databaseManager->transaction(function () use ($dto) {
            $this->waitlistEntryRepository->lockForProductPrice($dto->product_price_id);
            $this->validateNoDuplicate($dto);
            $position = $this->calculatePosition($dto);

            return $this->waitlistEntryRepository->create([
                'event_id' => $dto->event_id,
                'product_price_id' => $dto->product_price_id,
                'email' => EmailHelper::normalize($dto->email),
                'first_name' => trim($dto->first_name),
                'last_name' => $dto->last_name ? trim($dto->last_name) : null,
                'status' => WaitlistEntryStatus::WAITING->name,
                'cancel_token' => Str::random(64),
                'position' => $position,
                'locale' => $dto->locale,
            ]);
        });

        SendWaitlistConfirmationEmailJob::dispatch($entry);

        return $entry;
    }

    /**
     * @throws ResourceConflictException
     */
    private function validateWaitlistEnabled(ProductDomainObject $product): void
    {
        if ($product->getWaitlistEnabled() === false) {
            throw new ResourceConflictException(__('Waitlist is not enabled for this product'));
        }
    }

    /**
     * @throws ResourceConflictException
     */
    private function validateNoDuplicate(CreateWaitlistEntryDTO $dto): void
    {
        $conditions = [
            'email' => EmailHelper::normalize($dto->email),
            'event_id' => $dto->event_id,
            ['status', 'in', [WaitlistEntryStatus::WAITING->name, WaitlistEntryStatus::OFFERED->name]],
            'product_price_id' => $dto->product_price_id,
        ];

        $existing = $this->waitlistEntryRepository->findFirstWhere($conditions);

        if ($existing !== null) {
            throw new ResourceConflictException(
                __('You are already on the waitlist for this product')
            );
        }
    }

    private function calculatePosition(CreateWaitlistEntryDTO $dto): int
    {
        return $this->waitlistEntryRepository->getMaxPosition($dto->product_price_id) + 1;
    }
}
