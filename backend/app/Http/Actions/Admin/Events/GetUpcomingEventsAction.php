<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Admin\Events;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\Event\EventResource;
use TitaKita\Services\Application\Handlers\Admin\DTO\GetUpcomingEventsDTO;
use TitaKita\Services\Application\Handlers\Admin\GetUpcomingEventsHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetUpcomingEventsAction extends BaseAction
{
    public function __construct(
        private readonly GetUpcomingEventsHandler $handler,
    )
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $this->minimumAllowedRole(Role::SUPERADMIN);

        $events = $this->handler->handle(new GetUpcomingEventsDTO(
            perPage: min((int)$request->query('per_page', 20), 100),
        ));

        return $this->resourceResponse(
            resource: EventResource::class,
            data: $events
        );
    }
}
