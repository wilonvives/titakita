<?php

namespace TitaKita\Listeners\Event;

use TitaKita\Events\OrderStatusChangedEvent;
use TitaKita\Jobs\Event\UpdateEventStatisticsJob;

class UpdateEventStatsListener
{
    public function handle(OrderStatusChangedEvent $changedEvent): void
    {
        if (!$changedEvent->order->isOrderCompleted()) {
            return;
        }

        dispatch(new UpdateEventStatisticsJob($changedEvent->order));
    }
}
