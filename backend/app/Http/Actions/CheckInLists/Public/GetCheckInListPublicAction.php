<?php

namespace TitaKita\Http\Actions\CheckInLists\Public;

use TitaKita\Http\Actions\BaseAction;
use TitaKita\Resources\CheckInList\CheckInListResourcePublic;
use TitaKita\Services\Application\Handlers\CheckInList\Public\GetCheckInListPublicHandler;
use Illuminate\Http\JsonResponse;

class GetCheckInListPublicAction extends BaseAction
{
    public function __construct(
        private readonly GetCheckInListPublicHandler $getCheckInListPublicHandler,
    )
    {
    }

    public function __invoke(string $checkInListShortId): JsonResponse
    {
        $checkInList = $this->getCheckInListPublicHandler->handle($checkInListShortId);

        return $this->resourceResponse(
            resource: CheckInListResourcePublic::class,
            data: $checkInList,
        );
    }
}
