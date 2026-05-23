<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Admin\FailedJobs;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Application\Handlers\Admin\RetryFailedJobHandler;
use Illuminate\Http\JsonResponse;

class RetryFailedJobAction extends BaseAction
{
    public function __construct(
        private readonly RetryFailedJobHandler $handler,
    ) {
    }

    public function __invoke(int $jobId): JsonResponse
    {
        $this->minimumAllowedRole(Role::SUPERADMIN);

        $retried = $this->handler->handle($jobId);

        if (!$retried) {
            return $this->errorResponse(__('Failed job not found'), 404);
        }

        return $this->jsonResponse([
            'message' => __('Job queued for retry'),
        ]);
    }
}
