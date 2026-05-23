<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Admin\FailedJobs;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Resources\Admin\AdminFailedJobResource;
use TitaKita\Services\Application\Handlers\Admin\DTO\GetAllFailedJobsDTO;
use TitaKita\Services\Application\Handlers\Admin\GetAllFailedJobsHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetAllFailedJobsAction extends BaseAction
{
    public function __construct(
        private readonly GetAllFailedJobsHandler $handler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $this->minimumAllowedRole(Role::SUPERADMIN);

        $jobs = $this->handler->handle(new GetAllFailedJobsDTO(
            perPage: min((int)$request->query('per_page', 20), 100),
            search: $request->query('search'),
            queue: $request->query('queue'),
            sortBy: $request->query('sort_by', 'failed_at'),
            sortDirection: $request->query('sort_direction', 'desc'),
        ));

        return $this->resourceResponse(
            resource: AdminFailedJobResource::class,
            data: $jobs
        );
    }
}
