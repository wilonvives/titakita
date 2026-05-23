<?php

namespace TitaKita\Http\Actions\Accounts\Stripe;

use TitaKita\DomainObjects\AccountDomainObject;
use TitaKita\DomainObjects\Enums\Role;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Account\Stripe\StripeConnectAccountsResponseResource;
use TitaKita\Services\Application\Handlers\Account\Payment\Stripe\GetStripeConnectAccountsHandler;
use Illuminate\Http\JsonResponse;
use Throwable;

class GetStripeConnectAccountsAction extends BaseAction
{
    public function __construct(
        private readonly GetStripeConnectAccountsHandler $getStripeConnectAccountsHandler,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(int $accountId): JsonResponse
    {
        $this->isActionAuthorized($accountId, AccountDomainObject::class, Role::ADMIN);

        $result = $this->getStripeConnectAccountsHandler->handle($accountId);

        return $this->resourceResponse(
            resource: StripeConnectAccountsResponseResource::class,
            data: $result,
        );
    }
}
