<?php

namespace TitaKita\Http\Actions\Products;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Http\Request\Product\SortProductsRequest;
use TitaKita\Services\Application\Handlers\Product\SortProductsHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SortProductsAction extends BaseAction
{
    public function __construct(
        private readonly SortProductsHandler $sortProductsHandler
    )
    {
    }

    public function __invoke(SortProductsRequest $request, int $eventId): Response|JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $this->sortProductsHandler->handle(
                $eventId,
                $request->validated('sorted_categories'),
            );
        } catch (ResourceConflictException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_CONFLICT);
        }

        return $this->noContentResponse();
    }

}
