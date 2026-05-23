<?php

namespace TitaKita\Listeners\Order;

use TitaKita\Events\OrderStatusChangedEvent;
use TitaKita\Jobs\Order\SendOrderDetailsEmailJob;

class SendOrderDetailsEmailListener
{
    public function handle(OrderStatusChangedEvent $changedEvent): void
    {
        if (!$changedEvent->sendEmails) {
            return;
        }

        dispatch(new SendOrderDetailsEmailJob($changedEvent->order));
    }
}
