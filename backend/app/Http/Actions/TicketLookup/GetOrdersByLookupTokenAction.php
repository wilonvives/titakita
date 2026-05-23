<?php

namespace TitaKita\Http\Actions\TicketLookup;

use TitaKita\Exceptions\InvalidTicketLookupTokenException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Order\OrderResourcePublic;
use TitaKita\Services\Application\Handlers\TicketLookup\DTO\GetOrdersByLookupTokenDTO;
use TitaKita\Services\Application\Handlers\TicketLookup\GetOrdersByLookupTokenHandler;
use Illuminate\Http\JsonResponse;

class GetOrdersByLookupTokenAction extends BaseAction
{
    public function __construct(
        private readonly GetOrdersByLookupTokenHandler $getOrdersByLookupTokenHandler,
    ) {
    }

    public function __invoke(string $token): JsonResponse
    {
        try {
            $orders = $this->getOrdersByLookupTokenHandler->handle(
                new GetOrdersByLookupTokenDTO(
                    token: $token,
                )
            );

            return $this->resourceResponse(
                resource: OrderResourcePublic::class,
                data: $orders,
            );
        } catch (InvalidTicketLookupTokenException $e) {
            return $this->errorResponse(
                message: $e->getMessage(),
            );
        }
    }
}
