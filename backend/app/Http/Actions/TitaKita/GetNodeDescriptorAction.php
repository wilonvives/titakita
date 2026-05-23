<?php

namespace TitaKita\Http\Actions\TitaKita;

use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Domain\TitaKita\NodeDescriptorService;
use Illuminate\Http\JsonResponse;

/**
 * Serves the TitaKita node descriptor (proxied to /.well-known/titakita.json
 * by the SSR frontend). Public, unauthenticated.
 */
class GetNodeDescriptorAction extends BaseAction
{
    public function __construct(private readonly NodeDescriptorService $nodeDescriptorService)
    {
    }

    public function __invoke(): JsonResponse
    {
        return $this->jsonResponse($this->nodeDescriptorService->getDescriptor());
    }
}
