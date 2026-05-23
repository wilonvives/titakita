<?php

namespace TitaKita\Services\Domain\Order\DTO;

use Barryvdh\DomPDF\PDF;

class InvoicePdfResponseDTO
{
    public function __construct(
        public readonly PDF    $pdf,
        public readonly string $filename,
    )
    {
    }
}
