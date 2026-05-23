<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Orders\Public;

use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\Order\CompleteOrderRequest;
use TitaKita\Resources\Order\OrderResourcePublic;
use TitaKita\Services\Application\Handlers\Order\CompleteOrderHandler;
use TitaKita\Services\Application\Handlers\Order\DTO\CompleteOrderDTO;
use TitaKita\Services\Application\Handlers\Order\DTO\CompleteOrderOrderDTO;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CompleteOrderActionPublic extends BaseAction
{
    public function __construct(private readonly CompleteOrderHandler $orderService)
    {
    }

    public function __invoke(CompleteOrderRequest $request, int $eventId, string $orderShortId): JsonResponse
    {
        try {
            $order = $this->orderService->handle($orderShortId, CompleteOrderDTO::fromArray([
                'order' => CompleteOrderOrderDTO::fromArray([
                    'first_name' => $request->validated('order.first_name'),
                    'last_name' => $request->validated('order.last_name'),
                    'email' => $request->validated('order.email'),
                    'address' => $request->validated('order.address'),
                    'questions' => $request->has('order.questions')
                        ? $request->input('order.questions')
                        : null,
                    'opted_into_marketing' => $request->boolean('order.opted_into_marketing'),
                ]),
                'products' => $request->input('products'),
                'event_id' => $eventId,
            ]));
        } catch (ResourceConflictException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_CONFLICT);
        }

        return $this->resourceResponse(OrderResourcePublic::class, $order);
    }
}
