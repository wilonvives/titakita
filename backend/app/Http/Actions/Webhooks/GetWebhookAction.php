<?php

namespace TitaKita\Http\Actions\Webhooks;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Webhook\WebhookResource;
use TitaKita\Services\Application\Handlers\Webhook\GetWebhookHandler;
use Illuminate\Http\JsonResponse;

class GetWebhookAction extends BaseAction
{
    public function __construct(
        private readonly GetWebhookHandler $getWebhookHandler,
    )
    {
    }

    public function __invoke(int $eventId, int $webhookId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $webhook = $this->getWebhookHandler->handle(
            webhookId: $webhookId,
            accountId: $this->getAuthenticatedAccountId(),
            eventId: $eventId,
        );

        return $this->resourceResponse(
            resource: WebhookResource::class,
            data: $webhook
        );
    }
}
