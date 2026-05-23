<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Booking;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\ResponseCodes;
use TitaKita\Resources\Booking\ScheduleResource;
use TitaKita\Services\Application\Handlers\Booking\DTO\UpsertScheduleDTO;
use TitaKita\Services\Application\Handlers\Booking\UpsertScheduleHandler;
use TitaKita\Services\Domain\Booking\Exception\ScheduleRangeTooLongException;

class UpsertScheduleAction extends BaseAction
{
    public function __construct(private readonly UpsertScheduleHandler $handler) {}

    /**
     * @throws Throwable
     */
    public function __invoke(int $eventId, Request $request): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $request->merge(['event_id' => $eventId]);

        try {
            $result = $this->handler->handle(UpsertScheduleDTO::from($request->all()));
        } catch (ScheduleRangeTooLongException $e) {
            throw ValidationException::withMessages([
                'range_end_date' => $e->getMessage(),
            ]);
        }

        return $this->resourceResponse(
            resource: ScheduleResource::class,
            data: $result,
            statusCode: ResponseCodes::HTTP_CREATED,
        );
    }
}
