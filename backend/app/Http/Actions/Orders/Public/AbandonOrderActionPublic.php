<?php

namespace TitaKita\Http\Actions\Orders\Public;

use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Exceptions\UnauthorizedException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Order\OrderResourcePublic;
use TitaKita\Services\Application\Handlers\Order\Public\AbandonOrderPublicHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class AbandonOrderActionPublic extends BaseAction
{
    public function __construct(
        private readonly AbandonOrderPublicHandler $abandonOrderPublicHandler,
    )
    {
    }

    public function __invoke(int $eventId, string $orderShortId): JsonResponse
    {
        try {
            $order = $this->abandonOrderPublicHandler->handle($orderShortId);

            return $this->resourceResponse(
                resource: OrderResourcePublic::class,
                data: $order,
            );
        } catch (ResourceNotFoundException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (ResourceConflictException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_CONFLICT);
        } catch (UnauthorizedException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        }
    }
}
