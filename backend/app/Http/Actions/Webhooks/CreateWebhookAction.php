<?php

namespace TitaKita\Http\Actions\Webhooks;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\Status\WebhookStatus;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\Webhook\UpsertWebhookRequest;
use TitaKita\Resources\Webhook\WebhookResourceWithSecret;
use TitaKita\Services\Application\Handlers\Webhook\CreateWebhookHandler;
use TitaKita\Services\Application\Handlers\Webhook\DTO\CreateWebhookDTO;
use Illuminate\Http\JsonResponse;

class CreateWebhookAction extends BaseAction
{
    public function __construct(
        private readonly CreateWebhookHandler $createWebhookHandler,
    )
    {
    }

    public function __invoke(int $eventId, UpsertWebhookRequest $request): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $webhook = $this->createWebhookHandler->handle(
            new CreateWebhookDTO(
                url: $request->validated('url'),
                eventTypes: $request->validated('event_types'),
                userId: $this->getAuthenticatedUser()->getId(),
                accountId: $this->getAuthenticatedAccountId(),
                status: WebhookStatus::fromName($request->validated('status')),
                eventId: $eventId,
            )
        );

        return $this->resourceResponse(
            resource: WebhookResourceWithSecret::class,
            data: $webhook
        );
    }
}
