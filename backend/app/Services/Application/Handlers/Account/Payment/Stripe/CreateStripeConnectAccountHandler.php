<?php

namespace TitaKita\Services\Application\Handlers\Account\Payment\Stripe;

use TitaKita\DomainObjects\AccountDomainObject;
use TitaKita\DomainObjects\AccountStripePlatformDomainObject;
use TitaKita\DomainObjects\Enums\StripeConnectAccountType;
use TitaKita\DomainObjects\Enums\StripePlatform;
use TitaKita\DomainObjects\Generated\AccountStripePlatformDomainObjectAbstract;
use TitaKita\Exceptions\CreateStripeConnectAccountFailedException;
use TitaKita\Exceptions\CreateStripeConnectAccountLinksFailedException;
use TitaKita\Exceptions\SaasModeEnabledException;
use TitaKita\Exceptions\Stripe\StripeClientConfigurationException;
use TitaKita\Repository\Interfaces\AccountRepositoryInterface;
use TitaKita\Repository\Interfaces\AccountStripePlatformRepositoryInterface;
use TitaKita\Services\Application\Handlers\Account\Payment\Stripe\DTO\CreateStripeConnectAccountDTO;
use TitaKita\Services\Application\Handlers\Account\Payment\Stripe\DTO\CreateStripeConnectAccountResponse;
use TitaKita\Services\Domain\Payment\Stripe\StripeAccountSyncService;
use TitaKita\Services\Infrastructure\Stripe\StripeClientFactory;
use TitaKita\Services\Infrastructure\Stripe\StripeConfigurationService;
use Illuminate\Config\Repository;
use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;
use Stripe\Account;
use Stripe\StripeClient;
use Throwable;

class CreateStripeConnectAccountHandler
{
    public function __construct(
        private readonly AccountRepositoryInterface               $accountRepository,
        private readonly AccountStripePlatformRepositoryInterface $accountStripePlatformRepository,
        private readonly DatabaseManager                          $databaseManager,
        private readonly LoggerInterface                          $logger,
        private readonly Repository                               $config,
        private readonly StripeClientFactory                      $stripeClientFactory,
        private readonly StripeConfigurationService               $stripeConfigurationService,
        private readonly StripeAccountSyncService                 $stripeAccountSyncService,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function handle(CreateStripeConnectAccountDTO $command): CreateStripeConnectAccountResponse
    {
        if (!$this->config->get('app.saas_mode_enabled')) {
            throw new SaasModeEnabledException(
                __('Stripe Connect Account creation is only available in Saas Mode.'),
            );
        }

        return $this->databaseManager->transaction(fn() => $this->createOrGetStripeConnectAccount($command));
    }

    /**
     * @throws CreateStripeConnectAccountFailedException|CreateStripeConnectAccountLinksFailedException
     * @throws StripeClientConfigurationException
     */
    private function createOrGetStripeConnectAccount(CreateStripeConnectAccountDTO $command): CreateStripeConnectAccountResponse
    {
        $account = $this->accountRepository
            ->loadRelation(AccountStripePlatformDomainObject::class)
            ->findById($command->accountId);

        // If platform is explicitly specified (e.g., for Ireland migration), use it
        // Otherwise, use the primary platform from environment (Or null for open-source installations)
        if ($command->platform) {
            $platformToUse = StripePlatform::fromString($command->platform->value);
        } else {
            $platformToUse = $this->stripeConfigurationService->getPrimaryPlatform();
        }

        // Try to find existing platform record for the requested platform
        // This works for both null (open-source) and specific platforms
        $accountStripePlatform = $account->getStripePlatformByType($platformToUse);

        // Open-source installations without platform configuration should still work
        // They will use default Stripe keys instead of platform-specific ones
        $stripeClient = $this->stripeClientFactory->createForPlatform($platformToUse);

        $stripeConnectAccount = $this->getOrCreateStripeConnectAccount(
            account: $account,
            accountStripePlatform: $accountStripePlatform,
            stripeClient: $stripeClient,
            platform: $platformToUse,
        );

        $response = new CreateStripeConnectAccountResponse(
            stripeConnectAccountType: $stripeConnectAccount->type,
            stripeAccountId: $stripeConnectAccount->id,
            account: $account,
            isConnectSetupComplete: $this->stripeAccountSyncService->isStripeAccountComplete($stripeConnectAccount),
        );

        if ($response->isConnectSetupComplete) {
            // If setup is complete, but this isn't reflected in the account stripe platform, update it.
            if ($accountStripePlatform && $accountStripePlatform->getStripeSetupCompletedAt() === null) {
                $this->stripeAccountSyncService->markAccountAsComplete($accountStripePlatform, $stripeConnectAccount);
            }

            return $response;
        }

        $connectUrl = $this->stripeAccountSyncService->createStripeAccountSetupUrl($stripeConnectAccount, $stripeClient);
        if ($connectUrl === null) {
            throw new CreateStripeConnectAccountLinksFailedException(
                message: __('There are issues with creating the Stripe Connect Account Link. Please try again.'),
            );
        }

        $response->connectUrl = $connectUrl;

        return $response;
    }

    /**
     * @throws CreateStripeConnectAccountFailedException
     */
    private function getOrCreateStripeConnectAccount(
        AccountDomainObject                $account,
        ?AccountStripePlatformDomainObject $accountStripePlatform,
        StripeClient                       $stripeClient,
        ?StripePlatform                    $platform
    ): Account
    {
        try {
            if ($accountStripePlatform && $accountStripePlatform->getStripeAccountId() !== null) {
                return $stripeClient->accounts->retrieve($accountStripePlatform->getStripeAccountId());
            }

            $stripeAccount = $stripeClient->accounts->create([
                'type' => $this->config->get('app.stripe_connect_account_type')
                    ?? StripeConnectAccountType::EXPRESS->value,
            ]);
        } catch (Throwable $e) {
            $this->logger->error('Failed to create or fetch Stripe Connect Account: ' . $e->getMessage(), [
                'accountId' => $account->getId(),
                'stripeAccountId' => $accountStripePlatform?->getStripeAccountId() ?? 'null',
                'accountExists' => $accountStripePlatform?->getStripeAccountId() !== null ? 'true' : 'false',
                'platform' => $platform?->value ?? 'null',
                'exception' => $e,
            ]);

            throw new CreateStripeConnectAccountFailedException(
                message: __('There are issues with creating or fetching the Stripe Connect Account. Please try again.'),
                previous: $e,
            );
        }

        // Create or update account stripe platform record
        if (!$accountStripePlatform) {
            $this->accountStripePlatformRepository->create([
                AccountStripePlatformDomainObjectAbstract::ACCOUNT_ID => $account->getId(),
                AccountStripePlatformDomainObjectAbstract::STRIPE_ACCOUNT_ID => $stripeAccount->id,
                AccountStripePlatformDomainObjectAbstract::STRIPE_CONNECT_ACCOUNT_TYPE => $stripeAccount->type,
                AccountStripePlatformDomainObjectAbstract::STRIPE_CONNECT_PLATFORM => $platform?->value,
            ]);
        } else {
            $this->accountStripePlatformRepository->updateWhere(
                attributes: [
                    AccountStripePlatformDomainObjectAbstract::STRIPE_ACCOUNT_ID => $stripeAccount->id,
                    AccountStripePlatformDomainObjectAbstract::STRIPE_CONNECT_ACCOUNT_TYPE => $stripeAccount->type,
                ],
                where: [
                    'id' => $accountStripePlatform->getId(),
                ]
            );
        }

        return $stripeAccount;
    }


}
