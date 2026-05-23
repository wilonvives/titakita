<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Admin\FailedJobs;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Application\Handlers\Admin\DeleteFailedJobHandler;
use Illuminate\Http\JsonResponse;

class DeleteAllFailedJobsAction extends BaseAction
{
    public function __construct(
        private readonly DeleteFailedJobHandler $handler,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $this->minimumAllowedRole(Role::SUPERADMIN);

        $count = $this->handler->deleteAll();

        return $this->jsonResponse([
            'message' => __('Deleted :count failed jobs', ['count' => $count]),
            'deleted_count' => $count,
        ]);
    }
}
