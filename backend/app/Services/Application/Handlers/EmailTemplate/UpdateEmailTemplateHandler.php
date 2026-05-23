<?php

namespace TitaKita\Services\Application\Handlers\EmailTemplate;

use TitaKita\DomainObjects\EmailTemplateDomainObject;
use TitaKita\Exceptions\EmailTemplateNotFoundException;
use TitaKita\Exceptions\EmailTemplateValidationException;
use TitaKita\Exceptions\InvalidEmailTemplateException;
use TitaKita\Repository\Interfaces\EmailTemplateRepositoryInterface;
use TitaKita\Services\Application\Handlers\EmailTemplate\DTO\UpsertEmailTemplateDTO;
use TitaKita\Services\Domain\Email\EmailTemplateService;
use TitaKita\Services\Infrastructure\HtmlPurifier\HtmlPurifierService;

class UpdateEmailTemplateHandler
{
    public function __construct(
        private readonly EmailTemplateRepositoryInterface $emailTemplateRepository,
        private readonly EmailTemplateService $emailTemplateService,
        private readonly HtmlPurifierService $purifier,
    ) {
    }

    /**
     * @throws EmailTemplateValidationException
     * @throws EmailTemplateNotFoundException
     * @throws InvalidEmailTemplateException
     */
    public function handle(UpsertEmailTemplateDTO $dto): EmailTemplateDomainObject
    {
        if (!$dto->id) {
            throw new InvalidEmailTemplateException('Template ID is required for update');
        }

        $validation = $this->emailTemplateService->validateTemplate($dto->subject, $dto->body);
        if (!$validation['valid']) {
            $exception = new EmailTemplateValidationException('Template validation failed');
            $exception->validationErrors = $validation['errors'];
            throw $exception;
        }

        $template = $this->emailTemplateRepository->findFirstWhere([
            'id' => $dto->id,
            'account_id' => $dto->account_id,
        ]);

        if (!$template) {
            throw new EmailTemplateNotFoundException('Email template not found');
        }

        return $this->emailTemplateRepository->updateFromArray($template->getId(), [
            'subject' => $dto->subject,
            'body' => $this->purifier->purify($dto->body),
            'cta' => $dto->cta,
            'engine' => $dto->engine->value,
            'is_active' => $dto->is_active,
        ]);
    }
}
