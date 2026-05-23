<?php

namespace TitaKita\Services\Application\Handlers\SelfService;

use TitaKita\Repository\Interfaces\EventRepositoryInterface;
use TitaKita\Repository\Interfaces\OrderRepositoryInterface;
use TitaKita\Services\Application\Handlers\SelfService\DTO\ResendEmailPublicDTO;
use TitaKita\Services\Domain\SelfService\SelfServiceResendEmailService;

class ResendOrderConfirmationPublicHandler
{
    use SelfServiceValidationTrait;

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly EventRepositoryInterface $eventRepository,
        private readonly SelfServiceResendEmailService $selfServiceResendEmailService,
    ) {
    }

    public function handle(ResendEmailPublicDTO $dto): void
    {
        $this->loadAndValidateEvent($dto->eventId);
        $order = $this->loadAndValidateOrder($dto->orderShortId, $dto->eventId);

        $this->selfServiceResendEmailService->resendOrderConfirmation(
            orderId: $order->getId(),
            eventId: $dto->eventId,
            ipAddress: $dto->ipAddress,
            userAgent: $dto->userAgent
        );
    }
}
