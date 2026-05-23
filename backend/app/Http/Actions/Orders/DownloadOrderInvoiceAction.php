<?php

namespace TitaKita\Http\Actions\Orders;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Application\Handlers\Order\DTO\GetOrderInvoiceDTO;
use TitaKita\Services\Application\Handlers\Order\GetOrderInvoiceHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DownloadOrderInvoiceAction extends BaseAction
{
    public function __construct(
        private readonly GetOrderInvoiceHandler $orderInvoiceHandler,
    )
    {
    }

    public function __invoke(Request $request, int $eventId, int $orderId): Response
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $invoice = $this->orderInvoiceHandler->handle(new GetOrderInvoiceDTO(
            orderId: $orderId,
            eventId: $eventId,
        ));

        return $invoice->pdf->stream($invoice->filename);
    }
}
