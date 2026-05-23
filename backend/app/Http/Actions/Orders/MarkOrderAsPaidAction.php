<?php

namespace TitaKita\Http\Actions\Orders;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Order\OrderResource;
use TitaKita\Services\Application\Handlers\Order\DTO\MarkOrderAsPaidDTO;
use TitaKita\Services\Application\Handlers\Order\MarkOrderAsPaidHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class MarkOrderAsPaidAction extends BaseAction
{
    public function __construct(
        private readonly MarkOrderAsPaidHandler $markOrderAsPaidHandler,
    )
    {
    }

    public function __invoke(int $eventId, int $orderId): JsonResponse|Response
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $order = $this->markOrderAsPaidHandler->handle(new MarkOrderAsPaidDTO($eventId, $orderId));
        } catch (ResourceConflictException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_CONFLICT);
        }

        return $this->resourceResponse(
            resource: OrderResource::class,
            data: $order,
        );
    }
}
