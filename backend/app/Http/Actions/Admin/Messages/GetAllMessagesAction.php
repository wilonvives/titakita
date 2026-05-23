<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Admin\Messages;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Resources\Admin\AdminMessageResource;
use TitaKita\Services\Application\Handlers\Admin\DTO\GetAllMessagesForAdminDTO;
use TitaKita\Services\Application\Handlers\Admin\GetAllMessagesForAdminHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetAllMessagesAction extends BaseAction
{
    public function __construct(
        private readonly GetAllMessagesForAdminHandler $handler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $this->minimumAllowedRole(Role::SUPERADMIN);

        $messages = $this->handler->handle(new GetAllMessagesForAdminDTO(
            perPage: min((int)$request->query('per_page', 20), 100),
            search: $request->query('search'),
            status: $request->query('status'),
            type: $request->query('type'),
            sortBy: $request->query('sort_by', 'created_at'),
            sortDirection: $request->query('sort_direction', 'desc'),
        ));

        return $this->resourceResponse(
            resource: AdminMessageResource::class,
            data: $messages
        );
    }
}
