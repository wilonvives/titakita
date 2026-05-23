<?php

namespace TitaKita\Http\Actions\Attendees;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Exceptions\InvalidProductPriceId;
use TitaKita\Exceptions\NoTicketsAvailableException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\Attendee\CreateAttendeeRequest;
use TitaKita\Http\ResponseCodes;
use TitaKita\Resources\Attendee\AttendeeResource;
use TitaKita\Services\Application\Handlers\Attendee\CreateAttendeeHandler;
use TitaKita\Services\Application\Handlers\Attendee\DTO\CreateAttendeeDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateAttendeeAction extends BaseAction
{
    private CreateAttendeeHandler $createAttendeeHandler;

    public function __construct(CreateAttendeeHandler $createAttendeeHandler)
    {
        $this->createAttendeeHandler = $createAttendeeHandler;
    }

    /**
     * @throws ValidationException|Throwable
     */
    public function __invoke(CreateAttendeeRequest $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $attendee = $this->createAttendeeHandler->handle(CreateAttendeeDTO::fromArray(
                array_merge($request->validationData(), [
                    'event_id' => $eventId,
                ])
            ));
        } catch (NoTicketsAvailableException $exception) {
            throw ValidationException::withMessages([
                'product_id' => $exception->getMessage(),
            ]);
        } catch (InvalidProductPriceId $exception) {
            throw ValidationException::withMessages([
                'product_price_id' => $exception->getMessage(),
            ]);
        }

        return $this->resourceResponse(
            resource: AttendeeResource::class,
            data: $attendee,
            statusCode: ResponseCodes::HTTP_CREATED
        );
    }
}
