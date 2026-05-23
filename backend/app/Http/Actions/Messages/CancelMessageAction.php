<?php

namespace TitaKita\Http\Actions\Messages;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Message\MessageResource;
use TitaKita\Services\Application\Handlers\Message\CancelMessageHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CancelMessageAction extends BaseAction
{
    public function __construct(
        private readonly CancelMessageHandler $cancelMessageHandler,
    )
    {
    }

    public function __invoke(Request $request, int $eventId, int $messageId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $message = $this->cancelMessageHandler->handle($messageId, $eventId);

        return $this->resourceResponse(MessageResource::class, $message);
    }
}
