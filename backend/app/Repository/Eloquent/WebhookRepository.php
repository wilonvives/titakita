<?php

namespace TitaKita\Repository\Eloquent;

use TitaKita\DomainObjects\Status\WebhookStatus;
use TitaKita\DomainObjects\WebhookDomainObject;
use TitaKita\Models\Webhook;
use TitaKita\Repository\Interfaces\WebhookRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * @extends BaseRepository<WebhookDomainObject>
 */
class WebhookRepository extends BaseRepository implements WebhookRepositoryInterface
{
    protected function getModel(): string
    {
        return Webhook::class;
    }

    public function getDomainObject(): string
    {
        return WebhookDomainObject::class;
    }

    public function findEnabledByEventId(int $eventId): Collection
    {
        $results = $this->model::query()
            ->where('status', WebhookStatus::ENABLED->name)
            ->where(function ($query) use ($eventId) {
                $query->where('event_id', $eventId)
                    ->orWhere('organizer_id', function ($subquery) use ($eventId) {
                        $subquery->select('organizer_id')
                            ->from('events')
                            ->where('id', $eventId)
                            ->limit(1);
                    });
            })
            ->get();

        $this->resetModel();

        return $this->handleResults($results);
    }
}
