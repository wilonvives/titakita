<?php

namespace TitaKita\Services\Application\Handlers\Order;

use TitaKita\DomainObjects\OrderDomainObject;
use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Services\Application\Handlers\Order\DTO\MarkOrderAsPaidDTO;
use TitaKita\Services\Domain\Order\MarkOrderAsPaidService;
use Psr\Log\LoggerInterface;
use Throwable;

class MarkOrderAsPaidHandler
{
    public function __construct(
        private readonly MarkOrderAsPaidService $markOrderAsPaidService,
        private readonly LoggerInterface        $logger,
    )
    {
    }

    /**
     * @throws ResourceConflictException|Throwable
     */
    public function handle(MarkOrderAsPaidDTO $dto): OrderDomainObject
    {
        $this->logger->info(__('Marking order as paid'), [
            'orderId' => $dto->orderId,
            'eventId' => $dto->eventId,
        ]);

        return $this->markOrderAsPaidService->markOrderAsPaid(
            $dto->orderId,
            $dto->eventId,
        );
    }
}
