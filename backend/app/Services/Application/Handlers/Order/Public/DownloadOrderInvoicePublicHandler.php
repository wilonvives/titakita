<?php

namespace TitaKita\Services\Application\Handlers\Order\Public;

use TitaKita\Services\Domain\Order\DTO\InvoicePdfResponseDTO;
use TitaKita\Services\Domain\Order\GenerateOrderInvoicePDFService;

class DownloadOrderInvoicePublicHandler
{
    public function __construct(
        private readonly GenerateOrderInvoicePDFService $generateOrderInvoicePDFService,
    )
    {
    }

    public function handle(int $eventId, string $orderShortId): InvoicePdfResponseDTO
    {
        return $this->generateOrderInvoicePDFService->generatePdfFromOrderShortId(
            orderShortId: $orderShortId,
            eventId: $eventId,
        );
    }
}
