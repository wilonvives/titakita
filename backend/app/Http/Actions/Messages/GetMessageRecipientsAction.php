<?php

namespace TitaKita\Http\Actions\Messages;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Message\OutgoingMessageResource;
use TitaKita\Services\Application\Handlers\Message\GetMessageRecipientsHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetMessageRecipientsAction extends BaseAction
{
    public function __construct(
        private readonly GetMessageRecipientsHandler $handler,
    )
    {
    }

    public function __invoke(Request $request, int $eventId, int $messageId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $params = $this->getPaginationQueryParams($request);

        $recipients = $this->handler->handle($eventId, $messageId, $params);

        return $this->resourceResponse(OutgoingMessageResource::class, $recipients);
    }
}
