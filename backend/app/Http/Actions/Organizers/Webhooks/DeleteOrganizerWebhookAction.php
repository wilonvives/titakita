<?php

namespace TitaKita\Http\Actions\Organizers\Webhooks;

use TitaKita\DomainObjects\OrganizerDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Application\Handlers\Webhook\DeleteWebhookHandler;
use Illuminate\Http\Response;

class DeleteOrganizerWebhookAction extends BaseAction
{
    public function __construct(
        private readonly DeleteWebhookHandler $deleteWebhookHandler,
    )
    {
    }

    public function __invoke(int $organizerId, int $webhookId): Response
    {
        $this->isActionAuthorized($organizerId, OrganizerDomainObject::class);

        $this->deleteWebhookHandler->handle(
            webhookId: $webhookId,
            accountId: $this->getAuthenticatedAccountId(),
            eventId: null,
            organizerId: $organizerId,
        );

        return $this->deletedResponse();
    }
}
