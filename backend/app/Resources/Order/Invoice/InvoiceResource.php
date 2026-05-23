<?php

namespace TitaKita\Resources\Order\Invoice;

use TitaKita\DomainObjects\InvoiceDomainObject;
use TitaKita\Resources\BaseResource;

/** @mixin InvoiceDomainObject */
class InvoiceResource extends BaseResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->getId(),
            'invoice_number' => $this->getInvoiceNumber(),
            'order_id' => $this->getOrderId(),
            'status' => $this->getStatus(),
        ];
    }
}
