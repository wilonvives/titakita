<?php

namespace TitaKita\Http\Actions\EmailTemplates;

use TitaKita\DomainObjects\Enums\EmailTemplateType;
use TitaKita\DomainObjects\OrganizerDomainObject;
use TitaKita\Exceptions\AccountNotVerifiedException;
use TitaKita\Exceptions\EmailTemplateNotFoundException;
use TitaKita\Exceptions\EmailTemplateValidationException;
use TitaKita\Exceptions\InvalidEmailTemplateException;
use TitaKita\Http\Resources\EmailTemplateResource;
use TitaKita\Http\ResponseCodes;
use TitaKita\Services\Application\Handlers\EmailTemplate\UpdateEmailTemplateHandler;
use TitaKita\Services\Application\Handlers\EmailTemplate\DTO\UpsertEmailTemplateDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class UpdateOrganizerEmailTemplateAction extends BaseEmailTemplateAction
{
    public function __construct(
        private readonly UpdateEmailTemplateHandler $handler
    ) {
    }

    /**
     * @throws ValidationException
     */
    public function __invoke(Request $request, int $organizerId, int $templateId): JsonResponse
    {
        $this->isActionAuthorized($organizerId, OrganizerDomainObject::class);

        try {
            $this->verifyAccountCanModifyEmailTemplates();
        } catch (AccountNotVerifiedException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        }

        $validated = $this->validateUpdateEmailTemplateRequest($request);

        try {
            $cta = [
                'label' => $validated['ctaLabel'],
                'url_token' => 'order.url', // This will be determined by template type during update
            ];
            
            $template = $this->handler->handle(
                new UpsertEmailTemplateDTO(
                    account_id: $this->getAuthenticatedAccountId(),
                    template_type: EmailTemplateType::ORDER_CONFIRMATION, // This will be ignored in update
                    subject: $validated['subject'],
                    body: $validated['body'],
                    organizer_id: $organizerId,
                    event_id: null,
                    id: $templateId,
                    cta: $cta,
                    is_active: $validated['isActive'] ?? true,
                )
            );
        } catch (EmailTemplateValidationException $e) {
            throw ValidationException::withMessages($e->validationErrors ?: ['body' => $e->getMessage()]);
        } catch (InvalidEmailTemplateException $e) {
            throw ValidationException::withMessages([
                'id' => $e->getMessage(),
            ]);
        } catch (EmailTemplateNotFoundException $e) {
            return $this->errorResponse(
                message: $e->getMessage(),
                statusCode: ResponseCodes::HTTP_NOT_FOUND,
            );
        }

        return $this->resourceResponse(
            resource: EmailTemplateResource::class,
            data: $template,
        );
    }
}
