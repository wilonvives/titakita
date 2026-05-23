<?php

namespace TitaKita\Http\Actions\EventSettings;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Event\PlatformFeePreviewResource;
use TitaKita\Services\Application\Handlers\EventSettings\DTO\GetPlatformFeePreviewDTO;
use TitaKita\Services\Application\Handlers\EventSettings\GetPlatformFeePreviewHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetPlatformFeePreviewAction extends BaseAction
{
    public function __construct(
        private readonly GetPlatformFeePreviewHandler $handler,
    )
    {
    }

    public function __invoke(Request $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $this->validate($request, [
            'price' => 'required|numeric|min:0',
        ]);

        $dto = new GetPlatformFeePreviewDTO(
            eventId: $eventId,
            price: (float)$request->input('price'),
        );

        $result = $this->handler->handle($dto);

        return $this->resourceResponse(PlatformFeePreviewResource::class, $result);
    }
}
