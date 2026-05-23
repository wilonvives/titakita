<?php

namespace TitaKita\Http\Actions\EmailTemplates;

use TitaKita\DomainObjects\Enums\EmailTemplateType;
use TitaKita\DomainObjects\EventDomainObject;
use TitaKita\Exceptions\AccountNotVerifiedException;
use TitaKita\Exceptions\EmailTemplateValidationException;
use TitaKita\Exceptions\ResourceConflictException;
use TitaKita\Http\Resources\EmailTemplateResource;
use TitaKita\Http\ResponseCodes;
use TitaKita\Services\Application\Handlers\EmailTemplate\CreateEmailTemplateHandler;
use TitaKita\Services\Application\Handlers\EmailTemplate\DTO\UpsertEmailTemplateDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class CreateEventEmailTemplateAction extends BaseEmailTemplateAction
{
    public function __construct(
        private readonly CreateEmailTemplateHandler $handler
    )
    {
    }

    /**
     * @throws ValidationException
     */
    public function __invoke(Request $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        try {
            $this->verifyAccountCanModifyEmailTemplates();
        } catch (AccountNotVerifiedException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_UNAUTHORIZED);
        }

        $validated = $this->validateEmailTemplateRequest($request);

        try {
            $cta = [
                'label' => $validated['ctaLabel'],
                'url_token' => $validated['template_type'] === 'order_confirmation' ? 'order.url' : 'ticket.url',
            ];
            
            $template = $this->handler->handle(
                new UpsertEmailTemplateDTO(
                    account_id: $this->getAuthenticatedAccountId(),
                    template_type: EmailTemplateType::from($validated['template_type']),
                    subject: $validated['subject'],
                    body: $validated['body'],
                    organizer_id: null,
                    event_id: $eventId,
                    cta: $cta,
                    is_active: $validated['isActive'] ?? true,
                )
            );
        } catch (EmailTemplateValidationException $e) {
            throw ValidationException::withMessages($e->validationErrors ?: ['body' => $e->getMessage()]);
        } catch (ResourceConflictException $e) {
            return $this->errorResponse(
                message: $e->getMessage(),
                statusCode: ResponseCodes::HTTP_CONFLICT,
            );
        }

        return $this->resourceResponse(
            resource: EmailTemplateResource::class,
            data: $template,
            statusCode: ResponseCodes::HTTP_CREATED
        );
    }
}
