<?php

namespace TitaKita\Services\Application\Handlers\EmailTemplate;

use TitaKita\DomainObjects\Enums\EmailTemplateType;
use TitaKita\Services\Infrastructure\Email\LiquidTemplateRenderer;

class GetAvailableTokensHandler
{
    public function __construct(
        private readonly LiquidTemplateRenderer $liquidRenderer
    ) {
    }

    public function handle(EmailTemplateType $templateType): array
    {
        return $this->liquidRenderer->getAvailableTokens($templateType);
    }
}