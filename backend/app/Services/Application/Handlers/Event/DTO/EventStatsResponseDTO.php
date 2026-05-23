<?php

namespace TitaKita\Services\Application\Handlers\Event\DTO;

use TitaKita\DataTransferObjects\Attributes\CollectionOf;
use TitaKita\DataTransferObjects\BaseDTO;
use TitaKita\Services\Domain\Event\DTO\EventCheckInStatsResponseDTO;
use TitaKita\Services\Domain\Event\DTO\EventDailyStatsResponseDTO;
use Illuminate\Support\Collection;

class EventStatsResponseDTO extends BaseDTO
{
    public function __construct(
        #[CollectionOf(EventDailyStatsResponseDTO::class)]
        public readonly Collection          $daily_stats,
        public readonly string              $start_date,
        public readonly string              $end_date,

        public int                          $total_products_sold,
        public int                          $total_attendees_registered,

        public int                          $total_orders,
        public float                        $total_gross_sales,
        public float                        $total_fees,
        public float                        $total_tax,
        public float                        $total_views,
        public float                        $total_refunded,
    )
    {
    }
}
