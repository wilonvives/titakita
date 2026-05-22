<?php

namespace HiEvents\Http\Actions\TitaKita;

use HiEvents\Http\Actions\BaseAction;
use HiEvents\Resources\TitaKita\TitaKitaEventResource;
use HiEvents\Services\Application\Handlers\TitaKita\GetPublicEventsFeedHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public, versioned events feed for the TitaKita directory contract:
 * a schema.org-aligned list of this node's LIVE events, each with a canonical
 * backlink, wrapped with node identity + pagination. Public, unauthenticated.
 */
class GetPublicEventsFeedAction extends BaseAction
{
    public function __construct(private readonly GetPublicEventsFeedHandler $handler)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $events = $this->handler->handle($this->getPaginationQueryParams($request));

        return $this->jsonResponse([
            '@context' => 'https://schema.org',
            'node' => [
                'id' => config('titakita.node_id'),
                'name' => config('titakita.name'),
                'url' => rtrim((string)config('titakita.public_url'), '/'),
            ],
            'pagination' => [
                'page' => $events->currentPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
                'total_pages' => $events->lastPage(),
            ],
            'events' => $events->getCollection()
                ->map(fn($event) => (new TitaKitaEventResource($event))->toArray($request))
                ->all(),
        ]);
    }
}
