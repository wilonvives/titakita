<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Orders\Public;

use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\Order\CreateOrderRequest;
use TitaKita\Http\ResponseCodes;
use TitaKita\Resources\Order\OrderResourcePublic;
use TitaKita\Services\Application\Handlers\Order\CreateOrderHandler;
use TitaKita\Services\Application\Handlers\Order\DTO\CreateOrderPublicDTO;
use TitaKita\Services\Application\Handlers\Order\DTO\ProductOrderDetailsDTO;
use TitaKita\Services\Application\Locale\LocaleService;
use TitaKita\Services\Domain\Order\OrderCreateRequestValidationService;
use TitaKita\Services\Infrastructure\Session\CheckoutSessionManagementService;
use Illuminate\Http\JsonResponse;
use Throwable;

class CreateOrderActionPublic extends BaseAction
{
    public function __construct(
        private readonly CreateOrderHandler                  $orderHandler,
        private readonly OrderCreateRequestValidationService $orderCreateRequestValidationService,
        private readonly CheckoutSessionManagementService    $sessionIdentifierService,
        private readonly LocaleService                        $localeService,

    )
    {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(CreateOrderRequest $request, int $eventId): JsonResponse
    {
        $this->orderCreateRequestValidationService->validateRequestData($eventId, $request->all());
        $sessionId = $this->sessionIdentifierService->getSessionId();

        $order = $this->orderHandler->handle(
            eventId: $eventId,
            createOrderPublicDTO: CreateOrderPublicDTO::fromArray([
                'is_user_authenticated' => $this->isUserAuthenticated(),
                'promo_code' => $request->input('promo_code'),
                'affiliate_code' => $request->input('affiliate_code'),
                'products' => ProductOrderDetailsDTO::collectionFromArray($request->input('products')),
                'session_identifier' => $sessionId,
                'order_locale' => $this->localeService->getLocaleOrDefault($request->getPreferredLanguage()),
            ])
        );

        $order->setSessionIdentifier($sessionId);

        $response =  $this->resourceResponse(
            resource: OrderResourcePublic::class,
            data: $order,
            statusCode: ResponseCodes::HTTP_CREATED,
        );

        return $response->withCookie(
            cookie: $this->sessionIdentifierService->getSessionCookie(),
        );
    }
}
