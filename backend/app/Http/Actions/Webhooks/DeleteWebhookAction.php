<?php

namespace TitaKita\Http\Actions\Webhooks;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Application\Handlers\Webhook\DeleteWebhookHandler;
use Illuminate\Http\Response;

class DeleteWebhookAction extends BaseAction
{
    public function __construct(
        private readonly DeleteWebhookHandler $deleteWebhookHandler,
    )
    {
    }

    public function __invoke(int $eventId, int $webhookId): Response
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $this->deleteWebhookHandler->handle(
            webhookId: $webhookId,
            accountId: $this->getAuthenticatedAccountId(),
            eventId: $eventId,
        );

        return $this->deletedResponse();
    }
}
