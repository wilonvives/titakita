<?php

namespace TitaKita\Services\Application\Handlers\SelfService;

use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\OrderRepositoryInterface;
use TitaKita\Services\Application\Handlers\SelfService\DTO\EditOrderPublicDTO;
use TitaKita\Services\Domain\SelfService\DTO\EditOrderResultDTO;
use TitaKita\Services\Domain\SelfService\SelfServiceEditOrderService;

class EditOrderPublicHandler
{
    use SelfServiceValidationTrait;

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly EventRepositoryInterface $eventRepository,
        private readonly SelfServiceEditOrderService $selfServiceEditOrderService,
    ) {
    }

    public function handle(EditOrderPublicDTO $dto): EditOrderResultDTO
    {
        $this->loadAndValidateEvent($dto->eventId);
        $order = $this->loadAndValidateOrder($dto->orderShortId, $dto->eventId);

        return $this->selfServiceEditOrderService->editOrder(
            order: $order,
            firstName: $dto->firstName,
            lastName: $dto->lastName,
            email: $dto->email,
            ipAddress: $dto->ipAddress,
            userAgent: $dto->userAgent
        );
    }
}
