<?php

namespace TitaKita\Http\Actions\Orders;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\Order\EditOrderRequest;
use TitaKita\Resources\Order\OrderResource;
use TitaKita\Services\Application\Handlers\Order\DTO\EditOrderDTO;
use TitaKita\Services\Application\Handlers\Order\EditOrderHandler;
use Illuminate\Http\JsonResponse;

class EditOrderAction extends BaseAction
{
    public function __construct(
        private readonly EditOrderHandler $handler
    )
    {
    }

    public function __invoke(EditOrderRequest $request, int $eventId, int $orderId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $order = $this->handler->handle(new EditOrderDTO(
            id: $orderId,
            eventId: $eventId,
            firstName: $request->validated('first_name'),
            lastName: $request->validated('last_name'),
            email: $request->validated('email'),
            notes: $request->validated('notes'),
        ));

        return $this->resourceResponse(OrderResource::class, $order);
    }

}
