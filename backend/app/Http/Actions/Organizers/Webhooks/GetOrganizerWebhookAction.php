<?php

namespace TitaKita\Http\Actions\Organizers\Webhooks;

use TitaKita\DomainObjects\OrganizerDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Webhook\WebhookResource;
use TitaKita\Services\Application\Handlers\Webhook\GetWebhookHandler;
use Illuminate\Http\JsonResponse;

class GetOrganizerWebhookAction extends BaseAction
{
    public function __construct(
        private readonly GetWebhookHandler $getWebhookHandler,
    )
    {
    }

    public function __invoke(int $organizerId, int $webhookId): JsonResponse
    {
        $this->isActionAuthorized($organizerId, OrganizerDomainObject::class);

        $webhook = $this->getWebhookHandler->handle(
            webhookId: $webhookId,
            accountId: $this->getAuthenticatedAccountId(),
            eventId: null,
            organizerId: $organizerId,
        );

        return $this->resourceResponse(
            resource: WebhookResource::class,
            data: $webhook
        );
    }
}
