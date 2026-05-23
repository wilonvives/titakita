<?php

namespace TitaKita\Http\Actions\Orders;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\Message\SendMessageRequest;
use TitaKita\Jobs\Event\SendMessagesJob;
use Illuminate\Http\Response;

class MessageOrderAction extends BaseAction
{
    public function __invoke(SendMessageRequest $request, int $eventId, int $orderId): Response
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        SendMessagesJob::dispatch($orderId, $request->input('subject'), $request->input('message'));

        return $this->noContentResponse();
    }
}
