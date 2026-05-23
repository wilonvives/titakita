<?php

namespace TitaKita\Http\Actions\EmailTemplates;

use TitaKita\DomainObjects\OrganizerDomainObject;
use TitaKita\Exceptions\AccountNotVerifiedException;
use TitaKita\Exceptions\EmailTemplateNotFoundException;
use TitaKita\Http\ResponseCodes;
use TitaKita\Services\Application\Handlers\EmailTemplate\DeleteEmailTemplateHandler;
use TitaKita\Services\Application\Handlers\EmailTemplate\DTO\DeleteEmailTemplateDTO;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DeleteOrganizerEmailTemplateAction extends BaseEmailTemplateAction
{
    public function __construct(
        private readonly DeleteEmailTemplateHandler $handler
    ) {
    }

    public function __invoke(int $organizerId, int $templateId): JsonResponse
    {
        $this->isActionAuthorized($organizerId, OrganizerDomainObject::class);

        try {
            $this->verifyAccountCanModifyEmailTemplates();
        } catch (AccountNotVerifiedException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_UNAUTHORIZED);
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

        return response()->json(['message' => 'Template deleted successfully'], ResponseCodes::HTTP_OK);
    }
}