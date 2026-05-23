<?php

namespace TitaKita\Http\Actions\EmailTemplates;

use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Exceptions\AccountNotVerifiedException;
use TitaKita\Exceptions\EmailTemplateNotFoundException;
use TitaKita\Http\ResponseCodes;
use TitaKita\Services\Application\Handlers\EmailTemplate\DeleteEmailTemplateHandler;
use TitaKita\Services\Application\Handlers\EmailTemplate\DTO\DeleteEmailTemplateDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class DeleteEventEmailTemplateAction extends BaseEmailTemplateAction
{
    public function __construct(
        private readonly DeleteEmailTemplateHandler $handler
    )
    {
    }

    public function __invoke(int $eventId, int $templateId): Response|JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $this->verifyAccountCanModifyEmailTemplates();
        } catch (AccountNotVerifiedException $e) {
            return $this->errorResponse($e->getMessage(), SymfonyResponse::HTTP_UNAUTHORIZED);
        }

        try {
            $this->handler->handle(
                new DeleteEmailTemplateDTO(
                    id: $templateId,
                    account_id: $this->getAuthenticatedAccountId(),
                )
            );
        } catch (EmailTemplateNotFoundException $e) {
            return $this->errorResponse(
                message: $e->getMessage(),
                statusCode: ResponseCodes::HTTP_NOT_FOUND,
            );
        }

        return $this->deletedResponse();
    }
}
