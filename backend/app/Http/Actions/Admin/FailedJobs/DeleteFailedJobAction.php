<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Admin\FailedJobs;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Application\Handlers\Admin\DeleteFailedJobHandler;
use Illuminate\Http\JsonResponse;

class DeleteFailedJobAction extends BaseAction
{
    public function __construct(
        private readonly DeleteFailedJobHandler $handler,
    ) {
    }

    public function __invoke(int $jobId): JsonResponse
    {
        $this->minimumAllowedRole(Role::SUPERADMIN);

        $deleted = $this->handler->handle($jobId);

        if (!$deleted) {
            return $this->errorResponse(__('Failed job not found'), 404);
        }

        return $this->deletedResponse();
    }
}
