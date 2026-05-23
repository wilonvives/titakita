<?php

namespace TitaKita\Http\Actions\Waitlist\Public;

use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Exceptions\ResourceNotFoundException;
use TitaKita\Http\Actions\BaseAction;
use TitaKita\Services\Domain\Waitlist\CancelWaitlistEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CancelWaitlistEntryActionPublic extends BaseAction
{
    public function __construct(
        private readonly CancelWaitlistEntryService $cancelWaitlistEntryService,
    )
    {
    }

    public function __invoke(int $eventId, string $token): Response|JsonResponse
    {
        try {
            $this->cancelWaitlistEntryService->cancelByToken($token, $eventId);
        } catch (ResourceNotFoundException $e) {
            return $this->errorResponse(
                message: $e->getMessage(),
                statusCode: SymfonyResponse::HTTP_NOT_FOUND,
            );
        } catch (ResourceConflictException $e) {
            return $this->errorResponse(
                message: $e->getMessage(),
                statusCode: SymfonyResponse::HTTP_CONFLICT,
            );
        }

        return $this->noContentResponse();
    }
}
