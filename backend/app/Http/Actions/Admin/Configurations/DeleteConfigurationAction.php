<?php

declare(strict_types=1);

namespace TitaKita\Http\Actions\Admin\Configurations;

use TitaKita\DomainObjects\Enums\Role;
use TitaKita\Exceptions\CannotDeleteEntityException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Application\Handlers\Admin\DeleteConfigurationHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class DeleteConfigurationAction extends BaseAction
{
    public function __construct(
        private readonly DeleteConfigurationHandler $handler,
    ) {
    }

    public function __invoke(int $configurationId): Response
    {
        $this->minimumAllowedRole(Role::SUPERADMIN);

        try {
            $this->handler->handle($configurationId);
        } catch (CannotDeleteEntityException $e) {
            throw ValidationException::withMessages([
                'configuration' => [$e->getMessage()],
            ]);
        }

        return $this->deletedResponse();
    }
}
