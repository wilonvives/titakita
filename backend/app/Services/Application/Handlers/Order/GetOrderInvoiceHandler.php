<?php

namespace TitaKita\Services\Application\Handlers\Order;

use TitaKita\Services\Application\Handlers\Order\DTO\GetOrderInvoiceDTO;
use TitaKita\Services\Domain\Order\DTO\InvoicePdfResponseDTO;
use TitaKita\Services\Domain\Order\GenerateOrderInvoicePDFService;

class GetOrderInvoiceHandler
{
    public function __construct(
        private readonly GenerateOrderInvoicePDFService $generateOrderInvoicePDFService,
    )
    {
    }

    public function handle(GetOrderInvoiceDTO $command): InvoicePdfResponseDTO
    {
        return $this->generateOrderInvoicePDFService->generatePdfFromOrderId(
            orderId: $command->orderId,
            eventId: $command->eventId,
        );
    }
}
