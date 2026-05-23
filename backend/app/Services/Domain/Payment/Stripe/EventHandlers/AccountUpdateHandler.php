<?php

namespace TitaKita\Services\Domain\Payment\Stripe\EventHandlers;

use TitaKita\DomainObjects\AccountStripePlatformDomainObject;
use TitaKita\DomainObjects\Generated\AccountStripePlatformDomainObjectAbstract;
use TitaKita\Repository\Interfaces\AccountStripePlatformRepositoryInterface;
use TitaKita\Services\Domain\Payment\Stripe\StripeAccountSyncService;
use Stripe\Account;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class AccountUpdateHandler
{
    public function __construct(
        private readonly AccountStripePlatformRepositoryInterface $accountStripePlatformRepository,
        private readonly StripeAccountSyncService                 $stripeAccountSyncService,
    )
    {
    }

    public function handleEvent(Account $stripeAccount): void
    {
        /** @var AccountStripePlatformDomainObject $accountStripePlatform */
        $accountStripePlatform = $this->accountStripePlatformRepository->findFirstWhere([
            AccountStripePlatformDomainObjectAbstract::STRIPE_ACCOUNT_ID => $stripeAccount->id,
        ]);

        if ($accountStripePlatform === null) {
            throw new ResourceNotFoundException(
                sprintf('Account stripe platform with stripe account id %s not found', $stripeAccount->id)
            );
        }

        $this->stripeAccountSyncService->syncStripeAccountStatus($accountStripePlatform, $stripeAccount);
    }
}
