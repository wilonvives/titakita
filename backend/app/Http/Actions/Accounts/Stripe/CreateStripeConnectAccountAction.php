<?php

namespace TitaKita\Http\Actions\Accounts\Stripe;

use TitaKita\DomainObjects\AccountDomainObject;
use TitaKita\DomainObjects\Enums\Role;
use TitaKita\DomainObjects\Enums\StripePlatform;
use TitaKita\Exceptions\CreateStripeConnectAccountFailedException;
use TitaKita\Exceptions\CreateStripeConnectAccountLinksFailedException;
use TitaKita\Exceptions\SaasModeEnabledException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Account\Stripe\StripeConnectAccountResponseResource;
use TitaKita\Services\Application\Handlers\Account\Payment\Stripe\CreateStripeConnectAccountHandler;
use TitaKita\Services\Application\Handlers\Account\Payment\Stripe\DTO\CreateStripeConnectAccountDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CreateStripeConnectAccountAction extends BaseAction
{
    public function __construct(
        private readonly CreateStripeConnectAccountHandler $createStripeConnectAccountHandler,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(int $accountId, Request $request): JsonResponse
    {
        $this->isActionAuthorized($accountId, AccountDomainObject::class, Role::ADMIN);

        try {
            $accountResult = $this->createStripeConnectAccountHandler->handle(CreateStripeConnectAccountDTO::from([
                'accountId' => $this->getAuthenticatedAccountId(),
                'platform' => $request->has('platform')
                    ? StripePlatform::from($request->get('platform'))
                    : null,
            ]));
        } catch (CreateStripeConnectAccountLinksFailedException|CreateStripeConnectAccountFailedException $e) {
            return $this->errorResponse(
                message: $e->getMessage(),
                statusCode: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (SaasModeEnabledException $e) {
            return $this->errorResponse(
                message: $e->getMessage(),
                statusCode: Response::HTTP_FORBIDDEN
            );
        }

        return $this->resourceResponse(
            resource: StripeConnectAccountResponseResource::class,
            data: $accountResult
        );
    }
}
