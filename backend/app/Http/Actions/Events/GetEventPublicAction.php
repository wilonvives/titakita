<?php

namespace TitaKita\Http\Actions\Events;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\DomainObjects\Status\EventStatus;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Event\EventResourcePublic;
use TitaKita\Services\Application\Handlers\Event\DTO\GetPublicEventDTO;
use TitaKita\Services\Application\Handlers\Event\GetPublicEventHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\Log\LoggerInterface;

class GetEventPublicAction extends BaseAction
{
    public function __construct(
        private readonly GetPublicEventHandler $getPublicEventHandler,
        private readonly LoggerInterface       $logger,
    )
    {
    }

    public function __invoke(int $eventId, Request $request): Response|JsonResponse
    {
        $event = $this->getPublicEventHandler->handle(GetPublicEventDTO::fromArray([
            'eventId' => $eventId,
            'ipAddress' => $this->getClientIp($request),
            'promoCode' => strtolower($request->string('promo_code')),
            'isAuthenticated' => $this->isUserAuthenticated(),
        ]));

        if (!$this->canUserViewEvent($event)) {
            $this->logger->debug(__('Event with ID :eventId is not live and user is not authenticated', [
                'eventId' => $eventId
            ]));

            return $this->notFoundResponse();
        }

        return $this->resourceResponse(EventResourcePublic::class, $event);
    }

    private function canUserViewEvent(EventDomainObject $event): bool
    {
        if ($event->getStatus() === EventStatus::LIVE->name) {
            return true;
        }

        if ($this->isUserAuthenticated() && $event->getAccountId() === $this->getAuthenticatedAccountId()) {
            return true;
        }

        if ($this->isUserAuthenticated() && $this->getAuthenticatedUserRole() === Role::SUPERADMIN) {
            $this->logger->debug(__('Superadmin user is viewing non-live event with ID :eventId', [
                'eventId' => $event->getId(),
                'accountId' => $this->getAuthenticatedAccountId(),
            ]));
            return true;
        }

        return false;
    }
}
