<?php

namespace TitaKita\Http\Actions\Waitlist\Organizer;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Application\Handlers\Waitlist\GetWaitlistStatsHandler;
use Illuminate\Http\JsonResponse;

class GetWaitlistStatsAction extends BaseAction
{
    public function __construct(
        private readonly GetWaitlistStatsHandler $handler,
    )
    {
    }

    public function __invoke(int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $stats = $this->handler->handle($eventId);

        return $this->jsonResponse([
            'total' => $stats->total,
            'waiting' => $stats->waiting,
            'offered' => $stats->offered,
            'purchased' => $stats->purchased,
            'cancelled' => $stats->cancelled,
            'expired' => $stats->expired,
            'products' => array_map(fn($p) => [
                'product_price_id' => $p->product_price_id,
                'product_title' => $p->product_title,
                'waiting' => $p->waiting,
                'offered' => $p->offered,
                'available' => $p->available,
            ], $stats->products),
        ]);
    }
}
