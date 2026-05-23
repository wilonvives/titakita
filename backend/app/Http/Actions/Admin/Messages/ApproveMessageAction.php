<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Admin\Messages;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Application\Handlers\Admin\ApproveMessageHandler;
use Illuminate\Http\JsonResponse;

class ApproveMessageAction extends BaseAction
{
    public function __construct(
        private readonly ApproveMessageHandler $handler,
    ) {
    }

    public function __invoke(int $messageId): JsonResponse
    {
        $this->minimumAllowedRole(Role::SUPERADMIN);

        $this->handler->handle($messageId);

        return $this->jsonResponse([
            'message' => __('Message approved and queued for sending'),
        ]);
    }
}
