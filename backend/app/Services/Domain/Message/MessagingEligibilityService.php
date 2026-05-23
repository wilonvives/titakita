<?php

namespace TitaKita\Services\Domain\Message;

use Carbon\Carbon;
use TitaKita\DomainObjects\AccountMessagingTierDomainObject;
use TitaKita\DomainObjects\AccountStripePlatformDomainObject;
use TitaKita\DomainObjects\Enums\MessagingEligibilityFailureEnum;
use TitaKita\DomainObjects\Enums\MessagingTierViolationEnum;
use TitaKita\Repository\Interfaces\AccountMessagingTierRepositoryInterface;
use TitaKita\Repository\Interfaces\AccountRepositoryInterface;
use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\MessageRepositoryInterface;
use TitaKita\Repository\Interfaces\OrderRepositoryInterface;
use TitaKita\Services\Domain\Message\DTO\MessagingEligibilityFailureDTO;
use TitaKita\Services\Domain\Message\DTO\MessagingTierViolationDTO;

class MessagingEligibilityService
{
    private const UNTRUSTED_TIER_NAME = 'Untrusted';

    public function __construct(
        private readonly AccountRepositoryInterface $accountRepository,
        private readonly EventRepositoryInterface $eventRepository,
        private readonly MessageRepositoryInterface $messageRepository,
        private readonly AccountMessagingTierRepositoryInterface $accountMessagingTierRepository,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function checkEligibility(int $accountId, int $eventId): ?MessagingEligibilityFailureDTO
    {
        $account = $this->accountRepository
            ->loadRelation(AccountStripePlatformDomainObject::class)
            ->findById($accountId);

        $tier = $this->getAccountMessagingTier($account->getAccountMessagingTierId());

        // Trusted and Premium tiers bypass eligibility checks
        if ($tier->getName() !== self::UNTRUSTED_TIER_NAME) {
            return null;
        }

        $failures = [];

        if (!$account->isStripeSetupComplete()) {
            $failures[] = MessagingEligibilityFailureEnum::STRIPE_NOT_CONNECTED;
        }

        if (!$this->hasPaidOrder($accountId)) {
            $failures[] = MessagingEligibilityFailureEnum::NO_PAID_ORDERS;
        }

        $event = $this->eventRepository->findById($eventId);
        if ($this->isEventTooNew($event->getCreatedAt())) {
            $failures[] = MessagingEligibilityFailureEnum::EVENT_TOO_NEW;
        }

        if (empty($failures)) {
            return null;
        }

        return new MessagingEligibilityFailureDTO(
            accountId: $accountId,
            eventId: $eventId,
            failures: $failures,
        );
    }

    public function checkTierLimits(int $accountId, int $recipientCount, string $messageContent): ?MessagingTierViolationDTO
    {
        $violations = [];

        $account = $this->accountRepository->findById($accountId);
        $tier = $this->getAccountMessagingTier($account->getAccountMessagingTierId());

        $messagesInLast24h = $this->messageRepository->countMessagesInLast24Hours($accountId);
        if ($messagesInLast24h >= $tier->getMaxMessagesPer24h()) {
            $violations[] = MessagingTierViolationEnum::MESSAGE_LIMIT_EXCEEDED;
        }

        if ($recipientCount > $tier->getMaxRecipientsPerMessage()) {
            $violations[] = MessagingTierViolationEnum::RECIPIENT_LIMIT_EXCEEDED;
        }

        if (!$tier->getLinksAllowed() && $this->containsLinks($messageContent)) {
            $violations[] = MessagingTierViolationEnum::LINKS_NOT_ALLOWED;
        }

        if (empty($violations)) {
            return null;
        }

        return new MessagingTierViolationDTO(
            accountId: $accountId,
            tierName: $tier->getName(),
            violations: $violations,
        );
    }

    private function getAccountMessagingTier(?int $tierId): AccountMessagingTierDomainObject
    {
        if ($tierId !== null) {
            $tier = $this->accountMessagingTierRepository->findFirst($tierId);
            if ($tier !== null) {
                return $tier;
            }
        }

        return $this->accountMessagingTierRepository->findFirstWhere([
            'name' => self::UNTRUSTED_TIER_NAME,
        ]);
    }

    private function hasPaidOrder(int $accountId): bool
    {
        return $this->orderRepository->hasCompletedPaidOrderForAccount($accountId);
    }

    private function isEventTooNew(string $createdAt): bool
    {
        $eventCreatedAt = Carbon::parse($createdAt);
        $twentyFourHoursAgo = Carbon::now()->subHours(24);

        return $eventCreatedAt->isAfter($twentyFourHoursAgo);
    }

    private function containsLinks(string $content): bool
    {
        $urlPattern = '/https?:\/\/[^\s<>"\']+|href\s*=\s*["\'][^"\']+["\']/i';

        return (bool) preg_match($urlPattern, $content);
    }
}
