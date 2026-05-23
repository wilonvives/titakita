<?php

namespace TitaKita\Http\Actions\CheckInLists;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\CheckInList\UpsertCheckInListRequest;
use TitaKita\Resources\CheckInList\CheckInListResource;
use TitaKita\Services\Application\Handlers\CheckInList\DTO\UpsertCheckInListDTO;
use TitaKita\Services\Application\Handlers\CheckInList\UpdateCheckInlistHandler;
use TitaKita\Services\Domain\Product\Exception\UnrecognizedProductIdException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UpdateCheckInListAction extends BaseAction
{
    public function __construct(
        private readonly UpdateCheckInlistHandler $updateCheckInlistHandler,
    )
    {
    }

    public function __invoke(UpsertCheckInListRequest $request, int $eventId, int $checkInListId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $checkInList = $this->updateCheckInlistHandler->handle(
                new UpsertCheckInListDTO(
                    name: $request->validated('name'),
                    description: $request->validated('description'),
                    eventId: $eventId,
                    productIds: $request->validated('product_ids'),
                    expiresAt: $request->validated('expires_at'),
                    activatesAt: $request->validated('activates_at'),
                    id: $checkInListId,
                )
            );
        } catch (UnrecognizedProductIdException $exception) {
            return $this->errorResponse(
                message: $exception->getMessage(),
                statusCode: Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        return $this->resourceResponse(
            resource: CheckInListResource::class,
            data: $checkInList
        );
    }
}
