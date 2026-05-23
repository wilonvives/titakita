<?php

namespace TitaKita\Http\Actions\Webhooks;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\WebhookLogDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Webhook\WebhookLogResource;
use TitaKita\Services\Application\Handlers\Webhook\GetWebhookLogsHandler;
use Illuminate\Http\JsonResponse;

class GetWebhookLogsAction extends BaseAction
{
    public function __construct(
        private readonly GetWebhookLogsHandler $getWebhookLogsHandler,
    )
    {
    }

    public function __invoke(int $eventId, int $webhookId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $webhookLogs = $this->getWebhookLogsHandler->handle(
            webhookId: $webhookId,
            accountId: $this->getAuthenticatedAccountId(),
            eventId: $eventId,
        );

        $webhookLogs = $webhookLogs->sortBy(function (WebhookLogDomainObject $webhookLog) {
            return $webhookLog->getId();
        }, SORT_REGULAR, true);

        return $this->resourceResponse(
            resource: WebhookLogResource::class,
            data: $webhookLogs
        );
    }
}
