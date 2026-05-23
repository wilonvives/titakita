<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Events;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Exceptions\CannotDeleteEntityException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Application\Handlers\Event\DeleteEventHandler;
use TitaKita\Services\Application\Handlers\Event\DTO\DeleteEventDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class DeleteEventAction extends BaseAction
{
    public function __construct(
        private readonly DeleteEventHandler $deleteEventHandler,
    )
    {
    }

    public function __invoke(int $eventId): Response|JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class, Role::ADMIN);

        try {
            $this->deleteEventHandler->handle(DeleteEventDTO::fromArray([
                'eventId' => $eventId,
                'accountId' => $this->getAuthenticatedAccountId(),
            ]));
        } catch (CannotDeleteEntityException $exception) {
            return $this->errorResponse(
                message: $exception->getMessage(),
                statusCode: HttpResponse::HTTP_CONFLICT,
            );
        }

        return $this->deletedResponse();
    }
}
