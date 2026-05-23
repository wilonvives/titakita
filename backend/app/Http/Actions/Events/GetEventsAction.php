<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Events;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Event\EventResource;
use TitaKita\Services\Application\Handlers\Event\DTO\GetEventsDTO;
use TitaKita\Services\Application\Handlers\Event\GetEventsHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetEventsAction extends BaseAction
{
    public function __construct(
        private readonly GetEventsHandler $getEventsHandler,
    )
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $this->minimumAllowedRole(Role::ORGANIZER);

        $events = $this->getEventsHandler->handle(
            GetEventsDTO::fromArray([
                'accountId' => $this->getAuthenticatedAccountId(),
                'queryParams' => $this->getPaginationQueryParams($request),
            ]),
        );

        return $this->filterableResourceResponse(
            resource: EventResource::class,
            data: $events,
            domainObject: EventDomainObject::class,
        );
    }
}
