<?php

namespace TitaKita\Http\Actions\Webhooks;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\Status\WebhookStatus;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\Webhook\UpsertWebhookRequest;
use TitaKita\Resources\Webhook\WebhookResource;
use TitaKita\Services\Application\Handlers\Webhook\DTO\EditWebhookDTO;
use TitaKita\Services\Application\Handlers\Webhook\EditWebhookHandler;
use Illuminate\Http\JsonResponse;

class EditWebhookAction extends BaseAction
{
    public function __construct(
        private readonly EditWebhookHandler $editWebhookHandler,
    )
    {
    }

    public function __invoke(int $eventId, int $webhookId, UpsertWebhookRequest $request): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $webhook = $this->editWebhookHandler->handle(
            new EditWebhookDTO(
                webhookId: $webhookId,
                url: $request->validated('url'),
                eventTypes: $request->validated('event_types'),
                eventId: $eventId,
                userId: $this->getAuthenticatedUser()->getId(),
                accountId: $this->getAuthenticatedAccountId(),
                status: WebhookStatus::fromName($request->validated('status')),
            )
        );

        return $this->resourceResponse(
            resource: WebhookResource::class,
            data: $webhook
        );
    }
}
