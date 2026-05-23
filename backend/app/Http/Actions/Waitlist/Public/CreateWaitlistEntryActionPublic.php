<?php

namespace TitaKita\Http\Actions\Waitlist\Public;

use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\Waitlist\CreateWaitlistEntryRequest;
use TitaKita\Http\ResponseCodes;
use TitaKita\Resources\Waitlist\WaitlistEntryResource;
use TitaKita\Services\Application\Handlers\Waitlist\CreateWaitlistEntryHandler;
use TitaKita\Services\Application\Handlers\Waitlist\DTO\CreateWaitlistEntryDTO;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CreateWaitlistEntryActionPublic extends BaseAction
{
    public function __construct(
        private readonly CreateWaitlistEntryHandler $handler,
    )
    {
    }

    public function __invoke(CreateWaitlistEntryRequest $request, int $eventId): JsonResponse
    {
        try {
            $entry = $this->handler->handle(new CreateWaitlistEntryDTO(
                event_id: $eventId,
                product_price_id: $request->validated('product_price_id'),
                email: $request->validated('email'),
                first_name: $request->validated('first_name'),
                last_name: $request->validated('last_name'),
                locale: $request->input('locale', 'en'),
            ));
        } catch (ResourceConflictException $e) {
            return $this->errorResponse(
                message: $e->getMessage(),
                statusCode: Response::HTTP_CONFLICT,
            );
        }

        return $this->resourceResponse(
            resource: WaitlistEntryResource::class,
            data: $entry,
            statusCode: ResponseCodes::HTTP_CREATED,
        );
    }
}
