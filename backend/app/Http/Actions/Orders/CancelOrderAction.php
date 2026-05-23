<?php

namespace TitaKita\Http\Actions\Orders;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\Status\OrderStatus;
use TitaKita\Exceptions\RefundNotPossibleException;
use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Order\OrderResource;
use TitaKita\Services\Application\Handlers\Order\CancelOrderHandler;
use TitaKita\Services\Application\Handlers\Order\DTO\CancelOrderDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

class CancelOrderAction extends BaseAction
{
    public function __construct(
        private readonly CancelOrderHandler $cancelOrderHandler,
    )
    {
    }

    /**
     * @throws Throwable
     * @throws ValidationException
     */
    public function __invoke(int $eventId, int $orderId, Request $request): JsonResponse|Response
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $order = $this->cancelOrderHandler->handle(new CancelOrderDTO(
                eventId: $eventId,
                orderId: $orderId,
                refund: $request->boolean('refund')
            ));
        } catch (ResourceConflictException $e) {
            return $this->errorResponse($e->getMessage(), HttpResponse::HTTP_CONFLICT);
        } catch (ApiErrorException|RefundNotPossibleException $exception) {
            throw ValidationException::withMessages([
                'refund' => $exception instanceof ApiErrorException
                    ? 'Stripe error: ' . $exception->getMessage()
                    : $exception->getMessage(),
            ]);
        }

        return $this->resourceResponse(OrderResource::class, $order->setStatus(OrderStatus::CANCELLED->name));
    }
}
